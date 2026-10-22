<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GradeCalculationService;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Classe;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GradeCalculationTest extends TestCase
{
    use DatabaseTransactions;

    private GradeCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradeCalculationService();
    }

    /**
     * Test accurate weighted average computation with custom coefficients.
     * Formula: Sum(Note * Coeff) / Sum(Coeff)
     */
    public function test_compute_weighted_average_from_grades_collection(): void
    {
        // Grade 1: Note 16, Coeff 3 => 48
        // Grade 2: Note 12, Coeff 2 => 24
        // Grade 3: Note 14, Coeff 1 => 14
        // Total = 86 / 6 = 14.33
        $g1 = new Grade(['note' => 16.0, 'coefficient' => 3.0]);
        $g2 = new Grade(['note' => 12.0, 'coefficient' => 2.0]);
        $g3 = new Grade(['note' => 14.0, 'coefficient' => 1.0]);

        $grades = new EloquentCollection([$g1, $g2, $g3]);
        $avg = $this->service->computeWeightedAverageFromGrades($grades);

        $this->assertEquals(14.33, $avg);
    }

    /**
     * Test default coefficient of 1.0 when grade coefficient is zero or missing.
     */
    public function test_compute_weighted_average_uses_default_coefficient_when_null(): void
    {
        // Grade 1: Note 18, Coeff null (defaults to 1.0)
        // Grade 2: Note 12, Coeff null (defaults to 1.0)
        // Total = 30 / 2 = 15.00
        $g1 = new Grade(['note' => 18.0, 'coefficient' => null]);
        $g2 = new Grade(['note' => 12.0, 'coefficient' => 0.0]);

        $grades = new EloquentCollection([$g1, $g2]);
        $avg = $this->service->computeWeightedAverageFromGrades($grades);

        $this->assertEquals(15.00, $avg);
    }

    /**
     * Test empty grade collection returns 0.0 without errors.
     */
    public function test_empty_grades_returns_zero(): void
    {
        $empty = new EloquentCollection([]);
        $avg = $this->service->computeWeightedAverageFromGrades($empty);

        $this->assertEquals(0.0, $avg);
    }

    /**
     * Test academic mention mapping according to Moroccan/French educational standards.
     */
    public function test_academic_mentions(): void
    {
        $this->assertEquals('Excellent', $this->service->getMention(19.0));
        $this->assertEquals('Très Bien', $this->service->getMention(16.5));
        $this->assertEquals('Bien', $this->service->getMention(14.8));
        $this->assertEquals('Assez Bien', $this->service->getMention(12.5));
        $this->assertEquals('Passable', $this->service->getMention(10.5));
        $this->assertEquals('Ajourné', $this->service->getMention(8.5));
    }

    /**
     * Test calculateWeightedAverage using student ID and semester filter.
     */
    public function test_calculate_weighted_average_for_persisted_student(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        $teacher = \App\Models\User::whereIn('role', ['professeur', 'prof'])->first() ?? \App\Models\User::first();
        $this->assertNotNull($teacher);

        // Create two explicit grades in S1
        Grade::create([
            'student_id' => $student->idStudent,
            'teacher_id' => $teacher->id,
            'note' => 18.0,
            'coefficient' => 2.0,
            'semester' => 'S1',
            'type' => 'Contrôle 1',
            'subject_name' => 'Mathématiques',
            'exam_date' => now()->toDateString(),
        ]);

        Grade::create([
            'student_id' => $student->idStudent,
            'teacher_id' => $teacher->id,
            'note' => 12.0,
            'coefficient' => 2.0,
            'semester' => 'S1',
            'type' => 'Contrôle 2',
            'subject_name' => 'Physique',
            'exam_date' => now()->toDateString(),
        ]);

        $avgS1 = $this->service->calculateWeightedAverage($student->idStudent, 'S1');
        $this->assertGreaterThan(0.0, $avgS1);
    }
}
