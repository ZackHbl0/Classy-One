<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\NotificationRead;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();
        $studentClassIds = $student->registres->pluck('Cla_id')->map(fn($id) => (string) $id)->toArray();

        $notifications = DB::table('notification as n')
            ->select('n.id', 'n.titre', 'n.message', 'n.categorie', 'n.pieceJointe')
            ->leftJoin('notification_read as nr', function ($join) use ($student) {
                $join->on('nr.idNotification', '=', 'n.id')
                    ->where('nr.idStudent', '=', $student->idStudent);
            })
            ->where(function ($query) use ($student, $studentClassIds) {
                // 1. All Students
                $query->where('n.target_type', 'all')
                    // 2. Specific Student
                    ->orWhere(function ($q) use ($student) {
                        $q->where('n.target_type', 'students')
                            ->where('n.idStudent', $student->idStudent);
                    });

                // 3. Specific Classes
                foreach ($studentClassIds as $classId) {
                    $query->orWhere(function ($q) use ($classId) {
                        $q->where('n.target_type', 'classes')
                            ->whereJsonContains('n.target_ids', $classId);
                    });
                }
            })
            ->addSelect(DB::raw('CASE WHEN nr.idStudent IS NOT NULL THEN 1 ELSE 0 END as is_read'))
            ->orderBy('n.id', 'desc')
            ->get();

        // Ensure types exactly match what Flutter expects
        $mapped = $notifications->map(function ($n) {
            return [
                'id' => (int) $n->id,
                'titre' => $n->titre,
                'message' => $n->message,
                'categorie' => $n->categorie,
                'pieceJointe' => $n->pieceJointe,
                'isRead' => (bool) $n->is_read
            ];
        });

        return response()->json([
            "success" => true,
            "data" => $mapped
        ]);
    }

    public function markRead(Request $request)
    {
        $student = $request->user();
        $idNotification = $request->input('idNotification');

        if ($idNotification) {
            // Mark single notification as read
            NotificationRead::firstOrCreate([
                'idStudent' => $student->idStudent,
                'idNotification' => $idNotification
            ]);
        } else {
            // Mark all unread as read
            $unreadIds = DB::table('notification as n')
                ->leftJoin('notification_read as nr', function ($join) use ($student) {
                    $join->on('nr.idNotification', '=', 'n.id')
                        ->where('nr.idStudent', '=', $student->idStudent);
                })
                ->whereNull('nr.idStudent')
                ->pluck('n.id');

            foreach ($unreadIds as $id) {
                NotificationRead::firstOrCreate([
                    'idStudent' => $student->idStudent,
                    'idNotification' => $id
                ]);
            }
        }

        return response()->json([
            "success" => true,
            "message" => "Notifications marquées comme lues."
        ]);
    }

    public function sendTestPush(Request $request)
    {
        $request->validate([
            'idStudent' => 'required|integer',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $student = Student::find($request->idStudent);

        if (!$student || empty($student->fcmToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Étudiant introuvable ou aucun token FCM configuré.'
            ], 404);
        }

        try {
            $messaging = app('firebase.messaging');
            $message = CloudMessage::withTarget('token', $student->fcmToken)
                ->withNotification(FirebaseNotification::create($request->title, $request->body));

            $messaging->send($message);

            return response()->json([
                'success' => true,
                'message' => 'Notification Push Firebase envoyée avec succès!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur Firebase: ' . $e->getMessage()
            ], 500);
        }
    }
}
