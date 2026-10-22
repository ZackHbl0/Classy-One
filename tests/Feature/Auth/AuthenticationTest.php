<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\Student;
use App\Models\User;
use App\Models\SchoolParent;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test successful student login with valid matricule and password.
     */
    public function test_student_can_login_with_valid_matricule_and_password(): void
    {
        $student = Student::first();
        $this->assertNotNull($student, 'Student record should exist in database.');

        // Update password with a known test password
        $student->password = Hash::make('SecretPass123!');
        $student->save();

        $response = $this->postJson('/api/login', [
            'matricule' => $student->matricule,
            'password' => 'SecretPass123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Connexion réussie',
            ])
            ->assertJsonStructure([
                'status',
                'token',
                'student' => ['idStudent', 'matricule', 'nom', 'prenom'],
            ]);
    }

    /**
     * Test failed student login with incorrect password.
     */
    public function test_student_cannot_login_with_invalid_password(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        $response = $this->postJson('/api/login', [
            'matricule' => $student->matricule,
            'password' => 'CompletelyWrongPassword!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /**
     * Test failed student login with non-existent matricule.
     */
    public function test_student_cannot_login_with_nonexistent_matricule(): void
    {
        $response = $this->postJson('/api/login', [
            'matricule' => 'NON_EXISTENT_9999',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /**
     * Test successful professor login with matricule.
     */
    public function test_professor_can_login_with_valid_matricule(): void
    {
        $professor = User::whereIn('role', ['professeur', 'prof'])->first();
        $this->assertNotNull($professor, 'Professor should exist in database.');

        $professor->password = Hash::make('ProfPass123!');
        $professor->save();

        $response = $this->postJson('/api/professor/login', [
            'matricule' => $professor->matricule,
            'password' => 'ProfPass123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Connexion réussie',
            ])
            ->assertJsonStructure([
                'status',
                'token',
                'professor' => ['id', 'matricule', 'name', 'role'],
            ]);
    }

    /**
     * Test failed professor login with invalid credentials.
     */
    public function test_professor_cannot_login_with_invalid_credentials(): void
    {
        $professor = User::whereIn('role', ['professeur', 'prof'])->first();
        $this->assertNotNull($professor);

        $response = $this->postJson('/api/professor/login', [
            'matricule' => $professor->matricule,
            'password' => 'WrongPasswordProf!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /**
     * Test parent login with email.
     */
    public function test_parent_can_login_with_email_and_valid_password(): void
    {
        $parent = SchoolParent::first();
        $this->assertNotNull($parent, 'Parent record should exist in database.');

        $parent->password = Hash::make('ParentPass123!');
        $parent->save();

        $response = $this->postJson('/api/parent/login', [
            'email' => $parent->email,
            'password' => 'ParentPass123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Connexion réussie',
            ])
            ->assertJsonStructure([
                'status',
                'token',
                'parent' => ['id', 'name', 'email'],
                'children',
            ]);
    }

    /**
     * Test parent login with phone number.
     */
    public function test_parent_can_login_with_phone_number(): void
    {
        $parent = SchoolParent::whereNotNull('phone')->where('phone', '!=', '')->first();
        $this->assertNotNull($parent);

        $parent->password = Hash::make('ParentPhonePass123!');
        $parent->save();

        $response = $this->postJson('/api/parent/login', [
            'email' => $parent->phone, // The controller accepts phone via email param
            'password' => 'ParentPhonePass123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    /**
     * Test failed parent login with invalid password.
     */
    public function test_parent_cannot_login_with_invalid_password(): void
    {
        $parent = SchoolParent::first();
        $this->assertNotNull($parent);

        $response = $this->postJson('/api/parent/login', [
            'email' => $parent->email,
            'password' => 'IncorrectPassword!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /**
     * Test that Admin and Secretary credentials exist and are properly hashed.
     */
    public function test_admin_and_secretary_staff_accounts_exist_and_use_secure_hashes(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin, 'Admin account should exist.');
        $this->assertTrue(str_starts_with($admin->password, '$2y$') || str_starts_with($admin->password, '$2a$'), 'Admin password must be bcrypt hashed.');

        $secretary = User::where('role', 'secretaire')->first();
        $this->assertNotNull($secretary, 'Secretary account should exist.');
        $this->assertTrue(str_starts_with($secretary->password, '$2y$') || str_starts_with($secretary->password, '$2a$'), 'Secretary password must be bcrypt hashed.');
    }

    /**
     * Test logout revoking personal access token.
     */
    public function test_authenticated_student_can_logout(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        $student->tokens()->delete();
        $token = $student->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Déconnexion réussie',
            ]);

        $this->assertCount(0, $student->fresh()->tokens);
    }
}
