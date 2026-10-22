<?php

namespace Tests\Feature\Media;

use Tests\TestCase;
use App\Models\Student;
use App\Models\Course;
use App\Models\DocumentRequest;
use App\Models\Notification;
use App\Models\NotificationRead;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class SecureMediaAndNotificationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test course video streaming with valid temporary HMAC signature.
     */
    public function test_course_streaming_succeeds_with_valid_signature(): void
    {
        // Create mock video file on public disk
        Storage::disk('public')->put('courses/test_lesson.mp4', 'FAKE_MP4_BINARY_DATA_FOR_TESTING');

        $classe = \App\Models\Classe::first();
        $this->assertNotNull($classe);

        $prof = \App\Models\User::whereIn('role', ['professeur', 'prof'])->first() ?? \App\Models\User::first();
        $this->assertNotNull($prof);

        $course = Course::create([
            'title' => 'Test Video Course',
            'description' => 'Secure stream testing',
            'file_path' => 'courses/test_lesson.mp4',
            'classe_id' => $classe->id,
            'professor_id' => $prof->id,
        ]);

        $validSignedUrl = URL::temporarySignedRoute(
            'courses.stream',
            now()->addHours(2),
            [
                'course' => $course->id,
                'filename' => 'test_lesson.mp4',
            ]
        );

        $response = $this->actingAs($prof, 'sanctum')->get($validSignedUrl);
        // Should succeed with 200 or 206 partial content
        $this->assertContains($response->getStatusCode(), [200, 206]);
        $response->assertHeader('Accept-Ranges', 'bytes');
    }

    /**
     * Test course video streaming fails with 403 when signature is tampered.
     */
    public function test_course_streaming_fails_with_invalid_signature(): void
    {
        $course = Course::first();
        $this->assertNotNull($course);

        $validSignedUrl = URL::temporarySignedRoute(
            'courses.stream',
            now()->addHours(2),
            [
                'course' => $course->id,
                'filename' => 'video.mp4',
            ]
        );

        // Tamper with HMAC signature
        $tamperedUrl = $validSignedUrl . 'invalid_tampered_hash';

        $response = $this->get($tamperedUrl);
        $response->assertStatus(403);
    }

    /**
     * Test document download succeeds with valid HMAC signature.
     */
    public function test_document_download_succeeds_with_valid_signature(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        Storage::disk('local')->put('certificates/test_cert.pdf', '%PDF-1.4 Mock PDF content');

        $docRequest = DocumentRequest::create([
            'idStudent' => $student->idStudent,
            'document_type' => 'Certificat de Scolarité',
            'reason' => 'Test download',
            'urgency' => 'normal',
            'status' => 'ready',
            'file_url' => 'certificates/test_cert.pdf',
            'request_date' => now(),
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'documents.signed_download',
            now()->addHours(4),
            ['id' => $docRequest->id]
        );

        $response = $this->actingAs($student, 'sanctum')->get($signedUrl);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test document download fails with 403 when signature is tampered or expired.
     */
    public function test_document_download_fails_with_invalid_signature(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        $docRequest = DocumentRequest::create([
            'idStudent' => $student->idStudent,
            'document_type' => 'Certificat de Scolarité',
            'reason' => 'Test invalid signature',
            'urgency' => 'normal',
            'status' => 'ready',
            'request_date' => now(),
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'documents.signed_download',
            now()->addHours(4),
            ['id' => $docRequest->id]
        );

        $tamperedUrl = preg_replace('/signature=[a-f0-9]+/', 'signature=tampered_signature123', $signedUrl);

        $response = $this->get($tamperedUrl);
        $response->assertStatus(403);
    }

    /**
     * Test student notifications index and marking as read.
     */
    public function test_notifications_listing_and_mark_read(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        // Create a test global notification
        $notification = Notification::create([
            'titre' => 'Alerte Examen Test',
            'message' => 'Les examens débutent lundi prochain.',
            'categorie' => 'Examen',
            'target_type' => 'all',
        ]);

        $token = $student->createToken('student_notif_token', ['role:student'])->plainTextToken;

        // Retrieve notifications
        $indexResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/notifications');

        $indexResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Mark specific notification as read
        $readResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/notifications/mark-read', [
                'idNotification' => $notification->id,
            ]);

        $readResponse->assertStatus(200);

        // Assert record exists in notification_read
        $this->assertTrue(
            NotificationRead::where('idStudent', $student->idStudent)
                ->where('idNotification', $notification->id)
                ->exists(),
            'Notification must be recorded as read in notification_read table.'
        );
    }
}
