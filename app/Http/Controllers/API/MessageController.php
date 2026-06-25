<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'classe_id' => 'required|exists:classe,id',
        ]);

        $conversation = \App\Models\Conversation::create([
            'name' => $request->name,
            'type' => 'group',
            'classe_id' => $request->classe_id,
        ]);

        // Attach professor
        $conversation->users()->attach(Auth::id());

        // Get all students in this class
        $classe = \App\Models\Classe::with('registres')->find($request->classe_id);
        $studentIds = $classe->registres->pluck('idStudent')->filter()->unique();
        
        if ($studentIds->isNotEmpty()) {
            $conversation->students()->attach($studentIds);
        }

        return response()->json([
            'message' => 'Groupe créé avec succès.',
            'group' => $conversation
        ], 201);
    }

    public function getProfessors()
    {
        $users = User::whereIn('role', ['professor', 'professeur', 'admin'])->get(['id', 'name', 'role', 'last_seen_at']);
        $users->transform(function ($user) {
            $user->is_online = $user->isOnline();
            $user->last_seen_diff = $user->last_seen_at ? $user->last_seen_at->locale('fr')->diffForHumans() : 'Jamais connecté';
            return $user;
        });
        // Fetch groups for the authenticated student
        $student = Auth::guard('sanctum')->user();
        if ($student instanceof \App\Models\Student) {
            $groups = $student->conversations()->where('type', 'group')->get();
            $groupList = $groups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name ?? 'Groupe de classe',
                    'role' => 'group',
                    'is_online' => true,
                    'last_seen_diff' => 'Groupe de discussion',
                ];
            });
            $users = $users->concat($groupList);
        }

        return response()->json($users);
    }

    public function getChatStudents()
    {
        // Fetch all students from the student table
        $students = \App\Models\Student::all()->map(function ($student) {
            return [
                'id' => $student->idStudent,
                'name' => trim($student->nom . ' ' . $student->prenom),
                'role' => 'student',
                'is_online' => $student->isOnline(),
                'last_seen_diff' => $student->last_seen_at ? $student->last_seen_at->locale('fr')->diffForHumans() : 'Jamais connecté',
            ];
        });
        // Fetch groups for the authenticated professor
        $user = Auth::user();
        if ($user instanceof \App\Models\User) {
            $groups = $user->conversations()->where('type', 'group')->get();
            $groupList = $groups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name ?? 'Groupe',
                    'role' => 'group',
                    'is_online' => true,
                    'last_seen_diff' => 'Groupe de discussion',
                ];
            });
            $students = $students->concat($groupList);
        }
        
        return response()->json($students->values());
    }

    /**
     * Get chat history between the authenticated user and a target.
     *
     * IMPORTANT: The `sender_type` / `receiver_type` columns distinguish IDs
     * that belong to the `users` table ('user') from IDs that belong to the
     * `student` table ('student').  This prevents ID collisions between the
     * two tables (e.g. user.id=1 vs student.idStudent=1).
     *
     * The caller tells us what *kind* of entity the $targetId represents
     * via a query-string parameter `target_type` (defaults to 'student'
     * for backwards-compat with the Filament professor ↔ student chat).
     */
    public function getChatHistory(Request $request, $targetId)
    {
        // Detect who is calling: web auth (User model) or api/sanctum (could be Student)
        $currentId   = Auth::id();
        $currentType = $this->resolveAuthType();

        // The target type is passed as ?target_type=student|user (default: inferred)
        $targetType = $request->query('target_type');

        if (!$targetType) {
            // Auto-detect: if a professor/admin is calling, they chat with students.
            // If a student is calling, they chat with users (professors).
            $targetType = $currentType === 'user' ? 'student' : 'user';
        }

        if ($targetType === 'group') {
            $messages = Message::where('conversation_id', $targetId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            $messages->transform(function ($msg) {
                $msg->formatted_time = $msg->created_at->timezone('Africa/Casablanca')->format('H:i');
                $msg->tick_status = 'delivered';
                
                // Fetch sender name
                if ($msg->sender_type === 'student') {
                    $student = \App\Models\Student::find($msg->sender_id);
                    $msg->sender_name = $student ? $student->nom . ' ' . $student->prenom : 'Étudiant';
                } else {
                    $user = \App\Models\User::find($msg->sender_id);
                    $msg->sender_name = $user ? $user->name : 'Professeur';
                }

                // Convert old storage URLs to the new proxy URL for CORS
                if ($msg->attachment_url && str_contains($msg->attachment_url, '/storage/chat/')) {
                    $msg->attachment_url = str_replace('/storage/chat/', '/api/media/', $msg->attachment_url);
                }
                if ($msg->audio_url && str_contains($msg->audio_url, '/storage/chat/')) {
                    $msg->audio_url = str_replace('/storage/chat/', '/api/media/', $msg->audio_url);
                }

                return $msg;
            });

            return response()->json($messages);
        }

        $messages = Message::where(function ($query) use ($currentId, $currentType, $targetId, $targetType) {
                $query->where('sender_id', $currentId)
                      ->where('sender_type', $currentType)
                      ->where('receiver_id', $targetId)
                      ->where('receiver_type', $targetType);
            })
            ->orWhere(function ($query) use ($currentId, $currentType, $targetId, $targetType) {
                $query->where('sender_id', $targetId)
                      ->where('sender_type', $targetType)
                      ->where('receiver_id', $currentId)
                      ->where('receiver_type', $currentType);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $targetUser = $targetType === 'student' ? \App\Models\Student::find($targetId) : \App\Models\User::find($targetId);
        $isTargetOnline = $targetUser ? $targetUser->isOnline() : false;

        $messages->transform(function ($msg) use ($isTargetOnline) {
            $msg->formatted_time = $msg->created_at->timezone('Africa/Casablanca')->format('H:i');
            if ($msg->is_read) {
                $msg->tick_status = 'read';
            } elseif ($isTargetOnline) {
                $msg->tick_status = 'delivered';
            } else {
                $msg->tick_status = 'sent';
            }

            // Convert old storage URLs to the new proxy URL for CORS
            if ($msg->attachment_url && str_contains($msg->attachment_url, '/storage/chat/')) {
                $msg->attachment_url = str_replace('/storage/chat/', '/api/media/', $msg->attachment_url);
            }
            if ($msg->audio_url && str_contains($msg->audio_url, '/storage/chat/')) {
                $msg->audio_url = str_replace('/storage/chat/', '/api/media/', $msg->audio_url);
            }

            return $msg;
        });
            
        // Mark incoming messages as read
        Message::where('sender_id', $targetId)
            ->where('sender_type', $targetType)
            ->where('receiver_id', $currentId)
            ->where('receiver_type', $currentType)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $currentId   = Auth::id();
        $currentType = $this->resolveAuthType();

        // Determine receiver type: explicit param, or infer (user→student, student→user)
        $receiverType = $request->input('receiver_type');
        if (!$receiverType) {
            $receiverType = $currentType === 'user' ? 'student' : 'user';
        }

        // Validate receiver_id exists in the correct table
        if ($receiverType === 'group') {
            $request->validate([
                'receiver_id' => "required|exists:conversations,id",
                'message'     => 'nullable|string',
                'attachment'  => 'nullable|file|max:10240',
                'audio'       => 'nullable|file|max:10240',
            ]);
        } else {
            $receiverTable = $receiverType === 'student' ? 'student' : 'users';
            $receiverPk    = $receiverType === 'student' ? 'idStudent' : 'id';

            $request->validate([
                'receiver_id' => "required|exists:{$receiverTable},{$receiverPk}",
                'message'     => 'nullable|string',
                'attachment'  => 'nullable|file|max:10240',
                'audio'       => 'nullable|file|max:10240',
            ]);
        }

        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('chat/attachments', 'public');
            // Store a relative URL that hits our API proxy for CORS support
            $attachmentUrl = url('api/media/' . basename($path));
        }

        $audioUrl = null;
        if ($request->hasFile('audio') && $request->file('audio')->isValid()) {
            $path = $request->file('audio')->store('chat/audio', 'public');
            $audioUrl = url('api/media/' . basename($path));
        }

        $message = Message::create([
            'sender_id'      => $currentId,
            'sender_type'    => $currentType,
            'receiver_id'    => $receiverType === 'group' ? null : $request->receiver_id,
            'receiver_type'  => $receiverType === 'group' ? null : $receiverType,
            'conversation_id'=> $receiverType === 'group' ? $request->receiver_id : null,
            'message'        => $request->message ?? '',
            'attachment_url' => $attachmentUrl,
            'audio_url'      => $audioUrl,
            'is_read'        => false,
        ]);

        if ($receiverType === 'group') {
            $isReceiverOnline = true; // Assume delivered for groups
        } else {
            $receiverUser = $receiverType === 'student' ? \App\Models\Student::find($request->receiver_id) : \App\Models\User::find($request->receiver_id);
            $isReceiverOnline = $receiverUser ? $receiverUser->isOnline() : false;
        }

        $message->formatted_time = $message->created_at->timezone('Africa/Casablanca')->format('H:i');
        $message->tick_status = $isReceiverOnline ? 'delivered' : 'sent';

        return response()->json([
            'message' => 'Message sent successfully.',
            'data'    => $message,
        ], 201);
    }

    // ─── Helper ───────────────────────────────────────────────────

    /**
     * Figure out whether the currently authenticated entity is a User
     * (professor / admin / secrétaire) or a Student.
     */
    private function resolveAuthType(): string
    {
        $guard = Auth::guard('sanctum');
        if ($guard->check() && $guard->user() instanceof \App\Models\Student) {
            return 'student';
        }
        return 'user';
    }
}
