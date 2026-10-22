<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses for the student's class.
     */
    public function index(Request $request)
    {
        $student = $request->user();

        // Load the student's assigned class
        $classe = $student->classe;

        if (!$classe) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        // Fetch courses for the student's class, including the professor info
        $courses = Course::with('professor')
            ->where('classe_id', $classe->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $mapped = $courses->map(function ($course) {
            $streamUrl = null;
            if ($course->file_path) {
                // Generate secure HMAC-signed temporary streaming route (valid for 3 hours)
                $streamUrl = URL::temporarySignedRoute(
                    'courses.stream',
                    now()->addHours(3),
                    [
                        'course' => $course->id,
                        'filename' => rawurlencode(basename($course->file_path)),
                    ]
                );
            }

            return [
                'id' => (int) $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'file_path' => $course->file_path,
                'video_url' => $streamUrl,
                'professor_name' => $course->professor ? $course->professor->name : 'Professeur',
                'created_at' => $course->created_at ? $course->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mapped
        ]);
    }

    /**
     * Stream course video securely with HMAC-signed URL verification and HTTP 206 Partial Content.
     */
    public function streamVideo(Request $request, Course $course)
    {
        // 1. Verify HMAC cryptographic signature & expiry
        if (!$request->hasValidSignature()) {
            abort(403, 'Lien de streaming expiré ou signature invalide.');
        }

        // 2. Resolve authenticated user (session, bearer token or query token)
        $user = $request->user();
        if (!$user && ($tokenStr = $request->bearerToken() ?: $request->query('token'))) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenStr);
            if ($accessToken && (!$accessToken->expires_at || $accessToken->expires_at->isFuture())) {
                $user = $accessToken->tokenable;
                auth()->setUser($user);
            }
        }

        if (!$user) {
            abort(401, 'Non authentifié. Veuillez vous connecter pour accéder à ce cours.');
        }

        // 3. Strict business authorization checks (Staff, Professor owner, Student enrolled in class, Parent of student)
        $isAuthorized = false;

        // Staff (Admin / Secretaire)
        if (isset($user->role) && in_array($user->role, ['admin', 'secretaire'])) {
            $isAuthorized = true;
        }

        // Professor who created the course or teaches
        if (!$isAuthorized && isset($user->role) && in_array($user->role, ['professeur', 'prof'])) {
            if (!$course->professor_id || (int) $user->id === (int) $course->professor_id || (isset($user->idProfessor) && (int) $user->idProfessor === (int) $course->professor_id)) {
                $isAuthorized = true;
            }
        }

        // Student assigned to this course's class
        if (!$isAuthorized && isset($user->idStudent)) {
            if (!$course->classe_id) {
                $isAuthorized = true;
            } else {
                $studentClasseId = $user->classe?->id ?? \App\Models\Registre::where('idStudent', $user->idStudent)->value('idClasse');
                if ((int) $course->classe_id === (int) $studentClasseId) {
                    $isAuthorized = true;
                }
            }
        }

        // Parent linked to a child in this course's class
        if (!$isAuthorized && method_exists($user, 'students')) {
            if (!$course->classe_id) {
                $isAuthorized = true;
            } else {
                $isAuthorized = $user->students()
                    ->whereHas('registres', fn($q) => $q->where('idClasse', $course->classe_id))
                    ->exists();
            }
        }

        if (!$isAuthorized) {
            abort(403, 'Accès interdit. Vous n\'êtes pas inscrit à la classe dispensant ce cours.');
        }

        // 4. Prevent path traversal: strictly read the course's own file_path from database
        $path = $course->file_path;
        if (!$path) {
            abort(404, 'Aucun fichier vidéo associé à ce cours.');
        }

        // Look in private local disk first, fallback to public for backward compatibility
        $cleanPath = preg_replace('#^(public/|storage/|private/)#', '', ltrim($path, '/'));
        $disk = Storage::disk('local')->exists($cleanPath) ? Storage::disk('local') : (Storage::disk('public')->exists($cleanPath) ? Storage::disk('public') : null);
        if (!$disk) {
            abort(404, 'Fichier vidéo introuvable sur le serveur.');
        }

        $filePath = $disk->path($cleanPath);
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'video/mp4';

        $start = 0;
        $end = $fileSize - 1;
        $status = 200;
        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'X-Content-Type-Options' => 'nosniff',
        ];

        $range = $request->header('Range') ?: $request->server('HTTP_RANGE');
        if ($range && preg_match('/bytes=(\d+)-(\d*)/i', $range, $matches)) {
            $start = intval($matches[1]);
            if (!empty($matches[2])) {
                $end = intval($matches[2]);
            }
            $status = 206;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
        }

        $length = ($end - $start) + 1;
        $headers['Content-Length'] = $length;

        return response()->stream(function () use ($filePath, $start, $length) {
            $fp = fopen($filePath, 'rb');
            if ($fp === false) return;
            fseek($fp, $start);
            $bufferSize = 1024 * 64; // 64KB chunks
            $remaining = $length;

            while (!feof($fp) && $remaining > 0 && !connection_aborted()) {
                $read = min($bufferSize, $remaining);
                echo fread($fp, $read);
                flush();
                $remaining -= $read;
            }
            fclose($fp);
        }, $status, $headers);
    }
}
