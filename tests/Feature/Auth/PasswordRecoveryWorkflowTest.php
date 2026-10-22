<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\PasswordResetCode;
use App\Models\SchoolParent;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordRecoveryWorkflowTest extends TestCase
{
    /**
     * Test requesting a password reset with a valid student matricule.
     */
    public function test_student_can_request_password_reset_with_matricule(): void
    {
        $matricule = 'TEST_ST_' . time();
        $student = Student::create([
            'matricule' => $matricule,
            'nom' => 'Test',
            'prenom' => 'Student',
            'password' => Hash::make('old_password_123'),
            'telephone' => '0612345678',
        ]);

        $response = $this->postJson('/api/forgot-password', [
            'identifier' => $matricule,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'identifier' => $matricule,
            ]);

        $this->assertDatabaseHas('password_reset_codes', [
            'identifier' => $matricule,
            'user_type' => 'student',
            'user_id' => $student->idStudent,
        ]);

        // Clean up
        PasswordResetCode::where('identifier', $matricule)->delete();
        $student->delete();
    }

    /**
     * Test requesting a password reset with a valid parent email.
     */
    public function test_parent_can_request_password_reset_with_email(): void
    {
        $email = 'parent_test_' . time() . '@example.com';
        $parent = SchoolParent::create([
            'name' => 'Parent Test',
            'email' => $email,
            'phone' => '0698765432',
            'password' => Hash::make('parent_old_pass'),
        ]);

        $response = $this->postJson('/api/forgot-password', [
            'identifier' => $email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'identifier' => $email,
            ]);

        $this->assertDatabaseHas('password_reset_codes', [
            'identifier' => $email,
            'user_type' => 'parent',
            'user_id' => $parent->id,
        ]);

        // Clean up
        PasswordResetCode::where('identifier', $email)->delete();
        $parent->delete();
    }

    /**
     * Test non-existent identifier returns 404.
     */
    public function test_forgot_password_fails_for_unknown_identifier(): void
    {
        $response = $this->postJson('/api/forgot-password', [
            'identifier' => 'nonexistent_user_999999',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'status' => 'error',
            ]);
    }

    /**
     * Test complete 3-step recovery flow: request code, verify code, and reset password.
     */
    public function test_complete_recovery_flow_allows_login_with_new_password(): void
    {
        $matricule = 'REC_FLOW_' . time();
        $student = Student::create([
            'matricule' => $matricule,
            'nom' => 'Flow',
            'prenom' => 'Student',
            'password' => Hash::make('initial_secret_123'),
            'telephone' => '0622334455',
        ]);

        // Step 1: Request code
        $step1 = $this->postJson('/api/forgot-password', [
            'identifier' => $matricule,
        ]);
        $step1->assertStatus(200);

        $resetCode = PasswordResetCode::where('identifier', $matricule)->first();
        $this->assertNotNull($resetCode);
        $otp = $resetCode->code;

        // Step 2: Verify wrong code fails
        $wrongVerify = $this->postJson('/api/verify-reset-code', [
            'identifier' => $matricule,
            'code' => '000000',
        ]);
        $wrongVerify->assertStatus(422);

        // Step 2: Verify correct code succeeds
        $step2 = $this->postJson('/api/verify-reset-code', [
            'identifier' => $matricule,
            'code' => $otp,
        ]);
        $step2->assertStatus(200)
            ->assertJsonStructure(['success', 'reset_token']);

        $resetToken = $step2->json('reset_token');

        // Step 3: Reset password with confirmation
        $step3 = $this->postJson('/api/reset-password', [
            'identifier' => $matricule,
            'reset_token' => $resetToken,
            'password' => 'brand_new_secret_2026',
            'password_confirmation' => 'brand_new_secret_2026',
        ]);

        $step3->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify password in DB has changed and is verified by Hash
        $student->refresh();
        $this->assertTrue(Hash::check('brand_new_secret_2026', $student->password));

        // Step 4: Verify student can now login with new password
        $loginResponse = $this->postJson('/api/login', [
            'matricule' => $matricule,
            'password' => 'brand_new_secret_2026',
        ]);
        $loginResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'password_reset_completed',
        ]);

        // Clean up
        PasswordResetCode::where('identifier', $matricule)->delete();
        $student->delete();
    }
}
