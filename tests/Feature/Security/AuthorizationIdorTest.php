<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\Student;
use App\Models\User;
use App\Models\SchoolParent;
use App\Models\Absence;
use App\Models\DocumentRequest;
use App\Models\Classe;
use App\Models\Registre;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthorizationIdorTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * IDOR Test: Student A cannot justify Student B's absence.
     */
    public function test_student_cannot_justify_another_students_absence(): void
    {
        $students = Student::take(2)->get();
        $this->assertCount(2, $students, 'Requires at least 2 students in database.');

        $studentA = $students[0];
        $studentB = $students[1];

        $teacher = User::whereIn('role', ['professeur', 'prof'])->first();
        $classe = Classe::first();
        $this->assertNotNull($classe);

        // Create an absence for Student B
        $absenceB = Absence::create([
            'student_id' => $studentB->idStudent,
            'prof_id' => $teacher?->id ?? 11,
            'classe_id' => $classe->id,
            'matiere' => 'Mathématiques',
            'date' => now()->toDateString(),
            'seance' => '08:30 - 10:30',
            'is_justified' => false,
            'status' => 'pending',
        ]);

        $tokenA = $studentA->createToken('token_a', ['role:student'])->plainTextToken;

        // Student A attempts to justify Student B's absence
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->postJson("/api/student/absences/{$absenceB->id}/justify", [
                'reason' => 'Tentative de justification non autorisée',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Accès refusé. Cette absence ne vous appartient pas.',
            ]);
    }

    /**
     * IDOR Test: Student A cannot download Student B's document request.
     */
    public function test_student_cannot_download_another_students_document(): void
    {
        $students = Student::take(2)->get();
        $this->assertCount(2, $students);

        $studentA = $students[0];
        $studentB = $students[1];

        // Create ready document for Student B
        $docB = DocumentRequest::create([
            'idStudent' => $studentB->idStudent,
            'document_type' => 'Attestation de Scolarité',
            'reason' => 'Dossier administratif',
            'urgency' => 'normal',
            'status' => 'ready',
            'request_date' => now(),
        ]);

        $tokenA = $studentA->createToken('token_a', ['role:student'])->plainTextToken;

        // Student A attempts to download Student B's document via authenticated endpoint
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson("/api/documents/{$docB->id}/download");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Accès refusé. Vous n\'êtes pas autorisé à consulter ce document.',
            ]);
    }

    /**
     * IDOR Test: Parent cannot view details of an unrelated child.
     */
    public function test_parent_cannot_view_unrelated_student_details(): void
    {
        $parent = SchoolParent::first();
        $this->assertNotNull($parent);

        // Find a student NOT linked to this parent
        $linkedIds = $parent->students()->pluck('student.idStudent')->toArray();
        $unrelatedStudent = Student::whereNotIn('idStudent', $linkedIds)->first();
        $this->assertNotNull($unrelatedStudent, 'Requires an unrelated student.');

        $token = $parent->createToken('parent_token', ['role:parent'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/parent/children/{$unrelatedStudent->idStudent}");

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Accès refusé. Cet élève n\'est pas rattaché à votre compte parent.',
            ]);
    }

    /**
     * Access Control Test: Professor cannot enter grade for an unassigned class.
     */
    public function test_professor_cannot_enter_grade_for_unassigned_class(): void
    {
        $professor = User::whereIn('role', ['professeur', 'prof'])->first();
        $this->assertNotNull($professor);

        // Create a distinct class not assigned to the professor
        $unassignedClass = Classe::create([
            'nomClasse' => 'UNASSIGNED_TEST_' . time(),
            'niveau' => '2ème Année',
            'filiere' => 'Test Dev',
        ]);

        $course = \App\Models\Course::first();
        $this->assertNotNull($course);

        $student = Student::first();
        $token = $professor->createToken('prof_token', ['role:professor'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/professor/grades', [
                'student_id' => $student->idStudent,
                'course_id' => $course->id,
                'classe_id' => $unassignedClass->id,
                'note' => 15.0,
                'type' => 'Contrôle 1',
                'subject_name' => 'Informatique',
                'exam_date' => now()->toDateString(),
                'semester' => 'S1',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Accès refusé. Vous n\'êtes pas assigné à cette classe.',
            ]);
    }

    /**
     * Access Control Test: Professor cannot enter grade for a student not enrolled in that class.
     */
    public function test_professor_cannot_grade_student_not_enrolled_in_class(): void
    {
        $professor = User::whereIn('role', ['professeur', 'prof'])->first();
        $this->assertNotNull($professor);

        // Assign professor to a class
        $classe = Classe::first();
        $this->assertNotNull($classe);
        $professor->classes()->syncWithoutDetaching([$classe->id]);

        $course = \App\Models\Course::first();
        $this->assertNotNull($course);

        // Find student not enrolled in this class
        $enrolledIds = Registre::where('Cla_id', $classe->id)->pluck('idStudent')->toArray();
        $notEnrolledStudent = Student::whereNotIn('idStudent', $enrolledIds)->first();
        $this->assertNotNull($notEnrolledStudent);

        $token = $professor->createToken('prof_token', ['role:professor'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/professor/grades', [
                'student_id' => $notEnrolledStudent->idStudent,
                'course_id' => $course->id,
                'classe_id' => $classe->id,
                'note' => 14.0,
                'type' => 'Contrôle 1',
                'subject_name' => 'Informatique',
                'exam_date' => now()->toDateString(),
                'semester' => 'S1',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Accès refusé. Cet élève n\'est pas inscrit dans cette classe.',
            ]);
    }

    /**
     * Access Control Test: Professor cannot mark absence for an unassigned class.
     */
    public function test_professor_cannot_mark_absence_for_unassigned_class(): void
    {
        $professor = User::whereIn('role', ['professeur', 'prof'])->first();
        $this->assertNotNull($professor);

        // Create a distinct class not assigned to the professor
        $unassignedClass = Classe::create([
            'nomClasse' => 'UNASSIGNED_ABS_' . time(),
            'niveau' => '2ème Année',
            'filiere' => 'Test Dev',
        ]);

        $student = Student::first();
        $token = $professor->createToken('prof_token', ['role:professor'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/professor/absences', [
                'student_id' => $student->idStudent,
                'classe_id' => $unassignedClass->id,
                'matiere' => 'Mathématiques',
                'date' => now()->toDateString(),
                'seance' => '10:30 - 12:30',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Accès refusé. Vous n\'êtes pas assigné à cette classe.',
            ]);
    }

    /**
     * Access Control Test: Unauthenticated calls to protected routes are rejected.
     */
    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->postJson('/api/dashboard')->assertStatus(401);
        $this->postJson('/api/grades')->assertStatus(401);
        $this->getJson('/api/student/absences')->assertStatus(401);
        $this->getJson('/api/professor/students')->assertStatus(401);
        $this->getJson('/api/parent/dashboard')->assertStatus(401);
    }
}
