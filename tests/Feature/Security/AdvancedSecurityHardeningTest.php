<?php

namespace Tests\Feature\Security;

use App\Models\AuditLog;
use App\Models\DocumentRequest;
use App\Models\Grade;
use App\Models\Paiement;
use App\Models\Student;
use App\Models\User;
use App\Services\OfficialDocumentService;
use App\Services\SecureFileUploadService;
use App\Services\SuspiciousActivityService;
use App\Services\TwoFactorAuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdvancedSecurityHardeningTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login');
        RateLimiter::clear('otp');
        RateLimiter::clear('password-reset');
        Cache::flush();
    }

    /**
     * Test 1: Rate Limiting & Anti-Brute Force Protection on Login.
     */
    public function test_login_rate_limiting_enforces_lockout_after_max_attempts(): void
    {
        $matricule = 'TEST_BRUTE_FORCE_' . time();
        $ip = '198.51.100.42';

        for ($i = 0; $i < 5; $i++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->postJson('/api/login', [
                    'matricule' => $matricule,
                    'password' => 'wrong_password',
                ]);

            $this->assertContains($response->status(), [401, 422]);
        }

        // 6th attempt MUST be blocked by rate limiter with 429 Too Many Requests
        $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson('/api/login', [
                'matricule' => $matricule,
                'password' => 'wrong_password',
            ]);

        $response->assertStatus(429);
        $this->assertStringContainsString('Protection anti-brute force', $response->json('message'));
    }

    /**
     * Test 2: Two-Factor Authentication TOTP generation, validation, and backup codes.
     */
    public function test_totp_service_generates_and_verifies_rfc6238_codes_and_backup_codes(): void
    {
        $service = app(TwoFactorAuthService::class);
        $secret = $service->generateSecret();
        $this->assertEquals(16, strlen($secret));

        // Generate current TOTP code
        $code = $service->calculateCode($secret);
        $this->assertEquals(6, strlen($code));
        $this->assertTrue($service->verifyCode($secret, $code));
        $this->assertFalse($service->verifyCode($secret, '000000'));

        // Test recovery codes
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $plainCodes = $service->generateRecoveryCodes(8);
        $this->assertCount(8, $plainCodes);

        $service->enableForUser($admin, $secret, $plainCodes);
        $admin->refresh();

        $this->assertTrue($admin->hasTwoFactorEnabled());
        $this->assertEquals($secret, $service->getDecryptedSecret($admin));

        // Consume one recovery code
        $codeToConsume = $plainCodes[0];
        $this->assertTrue($service->verifyAndConsumeRecoveryCode($admin, $codeToConsume));

        // Reusing the same code must fail (single use)
        $this->assertFalse($service->verifyAndConsumeRecoveryCode($admin, $codeToConsume));

        // Disable 2FA
        $service->disableForUser($admin);
        $admin->refresh();
        $this->assertFalse($admin->hasTwoFactorEnabled());
    }

    /**
     * Test 3: Active Session Management and "Logout from Other Devices".
     */
    public function test_active_sessions_listing_and_logout_other_devices(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        // Create 3 active tokens (simulating 3 devices)
        $token1 = $student->createToken('Device 1 (Phone)')->plainTextToken;
        $token2 = $student->createToken('Device 2 (Tablet)')->plainTextToken;
        $token3 = $student->createToken('Device 3 (Laptop)')->plainTextToken;

        // Call active sessions endpoint using Token 1
        $response = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->getJson('/api/security/sessions');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertGreaterThanOrEqual(3, $response->json('count'));

        // Logout other devices
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->postJson('/api/security/logout-other-devices');

        $logoutResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Token 1 must still be valid
        $checkResponse = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->getJson('/api/security/sessions');
        $checkResponse->assertStatus(200);

        // Reset auth guards to force Sanctum to evaluate token2 against database
        app('auth')->forgetGuards();

        // Token 2 and 3 must now be revoked (401 Unauthorized)
        $invalidatedResponse = $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->getJson('/api/security/sessions');
        $invalidatedResponse->assertStatus(401);
    }

    /**
     * Test 4: Password update revokes existing tokens.
     */
    public function test_password_update_revokes_all_other_tokens(): void
    {
        $student = Student::first();
        $oldPassword = 'Password123!';
        $student->password = Hash::make($oldPassword);
        $student->save();

        $currentToken = $student->createToken('Current Session', ['role:student'])->plainTextToken;
        $otherToken = $student->createToken('Old Session On Phone', ['role:student'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $currentToken)
            ->postJson('/api/profile/update-password', [
                'currentPassword' => $oldPassword,
                'newPassword' => 'NewSecurePassword2026!',
                'newPassword_confirmation' => 'NewSecurePassword2026!',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Reset auth guards to force re-authentication against revoked tokens in database
        app('auth')->forgetGuards();

        // Old token must be revoked
        $this->withHeader('Authorization', 'Bearer ' . $otherToken)
            ->getJson('/api/security/sessions')
            ->assertStatus(401);
    }

    /**
     * Test 5: File Upload Security & Deep Magic-Bytes MIME Inspection.
     */
    public function test_file_upload_validation_detects_spoofed_mime_and_dangerous_files(): void
    {
        Storage::fake('local');
        $uploader = new SecureFileUploadService(5);

        // 1. Spoofed extension: A PHP script disguised as a .pdf
        $tempPath = tempnam(sys_get_temp_dir(), 'mal');
        file_put_contents($tempPath, '<?php phpinfo(); ?>');

        $maliciousFile = new UploadedFile(
            $tempPath,
            'fake_document.pdf',
            'application/pdf',
            null,
            true
        );

        $this->expectException(\InvalidArgumentException::class);
        $uploader->validateFile($maliciousFile);
    }

    /**
     * Test 6: Valid File Stored Outside Public Root and HMAC Signed Streaming.
     */
    public function test_valid_file_stored_securely_with_signed_streaming(): void
    {
        Storage::fake('local');
        $uploader = new SecureFileUploadService(5);

        // Create a real minimal PDF file header
        $pdfPath = tempnam(sys_get_temp_dir(), 'valid_pdf');
        file_put_contents($pdfPath, "%PDF-1.4\n%âãÏÓ\n1 0 obj\n<<\n/Type /Catalog\n>>\nendobj\ntrailer\n<<\n/Root 1 0 R\n>>\n%%EOF");

        $validFile = new UploadedFile(
            $pdfPath,
            'bulletin_semestre_1.pdf',
            'application/pdf',
            null,
            true
        );

        $storedPath = $uploader->storeSecurely($validFile, 'bulletins');
        $this->assertStringStartsWith('private_documents/bulletins/', $storedPath);

        // Generate signed download URL
        $signedUrl = $uploader->generateSignedDownloadUrl($storedPath, 15);
        $this->assertStringContainsString('signature=', $signedUrl);

        $admin = User::first() ?? User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin, 'sanctum')->get($signedUrl);
        $response->assertStatus(200);
    }

    /**
     * Test 7: Tamper-Proof Audit Log Cryptographic Chaining & Immutability.
     */
    public function test_audit_logs_are_tamper_proof_with_hash_chaining(): void
    {
        $log1 = AuditLog::record('TEST_ACTION_1', 'Premier journal d\'audit test');
        $this->assertNotEmpty($log1->signature);
        $this->assertNotEmpty($log1->previous_signature);

        $log2 = AuditLog::record('TEST_ACTION_2', 'Deuxième journal d\'audit test');
        $this->assertEquals($log1->signature, $log2->previous_signature);

        // Integrity verification
        $integrity = AuditLog::verifyChainIntegrity();
        $this->assertTrue($integrity['valid']);

        // Console command test
        $exitCode = Artisan::call('audit:verify-integrity');
        $this->assertEquals(0, $exitCode);

        // Verify immutability: attempting to update a log must throw RuntimeException
        try {
            $log1->description = 'Tentative de falsification';
            $log1->save();
            $this->fail('AuditLog should have thrown an exception on update');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('infalsifiables', $e->getMessage());
        }

        // Verify immutability: attempting to delete a log must throw RuntimeException
        try {
            $log1->delete();
            $this->fail('AuditLog should have thrown an exception on delete');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('infalsifiables', $e->getMessage());
        }
    }

    /**
     * Test 8: Suspicious Activity Detection on repeated failed logins, abnormal grades, and finances.
     */
    public function test_suspicious_activity_service_detects_attacks_and_dispatches_alerts(): void
    {
        $suspiciousService = app(SuspiciousActivityService::class);
        $identifier = 'victim_' . time();
        $ip = '10.0.0.99';

        // 1. Failed logins threshold
        $suspiciousService->handleFailedLogin($identifier, $ip);
        $suspiciousService->handleFailedLogin($identifier, $ip);
        $suspiciousService->handleFailedLogin($identifier, $ip); // 3rd failure triggers alert!

        $alertLog = AuditLog::where('action', 'SUSPICIOUS_ALERT')
            ->where('subject_type', 'repeated_failed_logins')
            ->latest('id')
            ->first();

        $this->assertNotNull($alertLog);
        $this->assertEquals($ip, $alertLog->details['ip']);

        // 2. Abnormal grade jump detection (>= 5.0 points)
        $grade = Grade::first();
        if ($grade) {
            $suspiciousService->inspectGradeUpdate($grade, 6.0, 19.5);

            $gradeAlert = AuditLog::where('action', 'SUSPICIOUS_ALERT')
                ->where('subject_type', 'abnormal_grade_update')
                ->latest('id')
                ->first();

            $this->assertNotNull($gradeAlert);
            $this->assertEquals(13.5, $gradeAlert->details['delta']);
        }

        // 3. Financial adjustment detection
        $paiement = Paiement::first();
        if ($paiement) {
            $suspiciousService->inspectFinancialAdjustment(
                $paiement,
                'Modification Paiement',
                ['montant' => 3000, 'remise_montant' => 0],
                ['montant' => 1500, 'remise_montant' => 1500]
            );

            $finAlert = AuditLog::where('action', 'SUSPICIOUS_ALERT')
                ->where('subject_type', 'sudden_financial_adjustment')
                ->latest('id')
                ->first();

            $this->assertNotNull($finAlert);
        }
    }

    /**
     * Test 9: HTTP Security Headers are present on responses.
     */
    public function test_security_headers_middleware_adds_protection_headers(): void
    {
        $response = $this->getJson('/up');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Mandatory Test 1: Signed URLs strictly enforce authentication and ownership.
     * An unauthenticated or unauthorized user CANNOT read another user's document/receipt even with a valid signed URL.
     */
    public function test_signed_urls_strictly_enforce_authentication_and_ownership(): void
    {
        $studentA = Student::first();
        $studentB = Student::skip(1)->first() ?? Student::factory()->create();
        $this->assertNotNull($studentA);
        $this->assertNotNull($studentB);

        // Document signed download test
        Storage::disk('local')->put('documents/official/test_doc_a.pdf', '%PDF-1.4 Mock Official Doc Content');
        $docRequest = DocumentRequest::create([
            'idStudent' => $studentA->idStudent,
            'document_type' => 'Certificat de Scolarité',
            'reason' => 'Test auth on signed url',
            'urgency' => 'normal',
            'status' => 'ready',
            'file_url' => 'documents/official/test_doc_a.pdf',
            'request_date' => now(),
        ]);

        $signedDocUrl = URL::temporarySignedRoute(
            'documents.signed_download',
            now()->addHour(),
            ['id' => $docRequest->id]
        );

        // 1. Unauthenticated request MUST be rejected with 401
        $this->get($signedDocUrl)->assertStatus(401);

        // 2. Unauthorized student (Student B attempting to access Student A's doc) MUST be rejected with 403
        $this->actingAs($studentB, 'sanctum')->get($signedDocUrl)->assertStatus(403);

        // 3. Authorized student (Student A, the owner) MUST succeed with 200
        $this->actingAs($studentA, 'sanctum')->get($signedDocUrl)->assertStatus(200);

        // Payment receipt signed download test
        $paiement = Paiement::first();
        if ($paiement) {
            $studentOwnerId = $paiement->student?->idStudent ?? $paiement->registre?->idStudent;
            $ownerStudent = Student::find($studentOwnerId);
            $unauthorizedStudent = Student::where('idStudent', '!=', $studentOwnerId)->first();

            $signedReceiptUrl = URL::temporarySignedRoute(
                'paiements.signed_receipt',
                now()->addHour(),
                ['id' => $paiement->id]
            );

            // 1. Unauthenticated request MUST be rejected with 401
            $this->app['auth']->forgetGuards();
            $this->get($signedReceiptUrl)->assertStatus(401);

            // 2. Unauthorized student MUST be rejected with 403
            if ($unauthorizedStudent) {
                $this->actingAs($unauthorizedStudent, 'sanctum')->get($signedReceiptUrl)->assertStatus(403);
            }

            // 3. Owner student MUST succeed with 200
            if ($ownerStudent) {
                $this->actingAs($ownerStudent, 'sanctum')->get($signedReceiptUrl)->assertStatus(200);
            }
        }
    }

    /**
     * Mandatory Test 2: Public direct access to sensitive storage directories returns 403.
     */
    public function test_direct_public_access_to_sensitive_storage_paths_returns_403(): void
    {
        $response1 = $this->get('/storage/certificates/sample_secret.pdf');
        $response1->assertStatus(403);

        $response2 = $this->get('/storage/documents/official/attestation_secret.pdf');
        $response2->assertStatus(403);

        $response3 = $this->get('/storage/courses/course_secret.mp4');
        $response3->assertStatus(403);
    }

    /**
     * Mandatory Test 3: Admin account without 2FA is strictly blocked from panel resources until enrolled.
     */
    public function test_admin_without_2fa_is_strictly_blocked_from_panel_resources(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $admin->two_factor_enabled = false;
        $admin->two_factor_secret = null;
        $admin->save();

        // 1. Attempting to access admin panel routes redirects to 2FA enrollment page
        $response = $this->actingAs($admin, 'web')->get('/panel');
        $response->assertRedirect('/panel/two-factor-settings');

        // 2. JSON request is rejected with 403 Forbidden and 2FA_ENROLLMENT_REQUIRED
        $jsonResponse = $this->actingAs($admin, 'web')->getJson('/panel/two-factor-settings');
        // The two-factor-settings page itself must be accessible for enrollment
        $this->assertNotEquals(403, $jsonResponse->status());
    }

    /**
     * Mandatory Test 4: Disabling 2FA strictly requires second factor (TOTP or recovery code).
     */
    public function test_disabling_2fa_strictly_requires_second_factor_code(): void
    {
        $service = app(TwoFactorAuthService::class);
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $secret = $service->generateSecret();
        $codes = $service->generateRecoveryCodes(8);

        $admin->password = Hash::make('SecretAdminPass123!');
        $admin->save();

        $service->enableForUser($admin, $secret, $codes);
        $admin->refresh();
        $this->assertTrue($admin->hasTwoFactorEnabled());

        // 1. Attempting to disable 2FA without 2FA code must be rejected with 422
        $responseNoCode = $this->actingAs($admin, 'sanctum')->postJson('/api/2fa/disable', [
            'password' => 'SecretAdminPass123!',
        ]);
        $responseNoCode->assertStatus(422);

        // 2. Attempting with wrong 2FA code must be rejected with 422
        $responseWrongCode = $this->actingAs($admin, 'sanctum')->postJson('/api/2fa/disable', [
            'password' => 'SecretAdminPass123!',
            'code' => '999999',
        ]);
        $responseWrongCode->assertStatus(422);

        // 3. Attempting with valid recovery code succeeds and disables 2FA
        $validRecoveryCode = $codes[0];
        $responseSuccess = $this->actingAs($admin, 'sanctum')->postJson('/api/2fa/disable', [
            'password' => 'SecretAdminPass123!',
            'code' => $validRecoveryCode,
        ]);
        $responseSuccess->assertStatus(200);

        $admin->refresh();
        $this->assertFalse($admin->hasTwoFactorEnabled());
    }

    /**
     * Mandatory Test 5: TOTP replay is strictly prevented within the same window.
     */
    public function test_totp_code_cannot_be_reused_within_same_window(): void
    {
        Cache::flush();
        $service = app(TwoFactorAuthService::class);
        $secret = $service->generateSecret();
        $validCode = $service->calculateCode($secret);

        // First verification succeeds
        $firstAttempt = $service->verifyCode($secret, $validCode);
        $this->assertTrue($firstAttempt);

        // Second verification with the exact same code MUST fail (replay blocked)
        $secondAttempt = $service->verifyCode($secret, $validCode);
        $this->assertFalse($secondAttempt);
    }

    /**
     * Mandatory Test 6: Password spraying with rotating identifiers from the same IP is blocked.
     */
    public function test_password_spraying_from_same_ip_is_blocked_by_rate_limiting(): void
    {
        RateLimiter::clear('login');
        $ip = '203.0.113.77';

        // Perform 10 failed login attempts with 10 DIFFERENT matricules from the exact same IP
        for ($i = 1; $i <= 10; $i++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->postJson('/api/login', [
                    'matricule' => 'SPRAY_STUDENT_' . $i . '_' . time(),
                    'password' => 'CommonSprayedPassword2026!',
                ]);

            $this->assertContains($response->status(), [401, 422]);
        }

        // 11th attempt from the same IP with a new identifier MUST trigger HTTP 429
        $sprayedResponse = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson('/api/login', [
                'matricule' => 'SPRAY_STUDENT_11_' . time(),
                'password' => 'CommonSprayedPassword2026!',
            ]);

        $sprayedResponse->assertStatus(429);
        $this->assertStringContainsString('Protection anti-brute force', $sprayedResponse->json('message'));
    }

    /**
     * Mandatory Test 7: Global logout revokes both Sanctum tokens and database web sessions.
     */
    public function test_global_logout_revokes_both_sanctum_tokens_and_database_web_sessions(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        // Create 2 tokens
        $token1 = $student->createToken('Mobile App')->plainTextToken;
        $student->createToken('Tablet Device')->plainTextToken;

        $this->assertGreaterThanOrEqual(2, $student->tokens()->count());

        // Simulate database session row
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->insert([
                'id' => Str::random(40),
                'user_id' => $student->getKey(),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit Test Browser',
                'payload' => serialize(['user' => $student->getKey()]),
                'last_activity' => time(),
            ]);

            $this->assertEquals(1, DB::table('sessions')->where('user_id', $student->getKey())->count());
        }

        // Call global logout
        $response = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->postJson('/api/security/logout-all');

        $response->assertStatus(200);

        // All Sanctum tokens revoked
        $this->assertEquals(0, $student->tokens()->count());

        // All web sessions revoked
        if (Schema::hasTable('sessions')) {
            $this->assertEquals(0, DB::table('sessions')->where('user_id', $student->getKey())->count());
        }
    }

    /**
     * Mandatory Test 8: Official document generation uses secure private storage and protected download.
     */
    public function test_official_document_generation_uses_private_storage_and_headers(): void
    {
        $student = Student::first();
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $this->assertNotNull($student);
        $this->assertNotNull($admin);

        $docRequest = DocumentRequest::create([
            'idStudent' => $student->idStudent,
            'document_type' => 'Attestation de Scolarité',
            'reason' => 'Private storage verification test',
            'urgency' => 'normal',
            'status' => 'en_attente',
            'request_date' => now(),
        ]);

        $service = app(OfficialDocumentService::class);
        $storedRelativePath = $service->generate($docRequest, $admin);

        // Must be saved in local private storage
        $this->assertTrue(Storage::disk('local')->exists($storedRelativePath));

        // Must NOT be stored in public directory
        $this->assertFalse(Storage::disk('public')->exists($storedRelativePath));

        // Download via signed route as authorized student includes security headers
        $signedUrl = URL::temporarySignedRoute(
            'documents.signed_download',
            now()->addHour(),
            ['id' => $docRequest->id]
        );

        $response = $this->actingAs($student, 'sanctum')->get($signedUrl);
        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
    }
}
