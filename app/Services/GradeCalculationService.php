<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Collection;

class GradeCalculationService
{
    /**
     * Calculate the weighted average (Moyenne Générale Pondérée) for a student
     * in a given semester, using subject coefficients.
     *
     * Formula: Sum(Subject_Average * Coefficient) / Sum(Coefficients)
     *
     * @param  int         $studentId
     * @param  string|null $semester  'S1', 'S2', or null for all semesters
     * @return float
     */
    public function calculateWeightedAverage(int $studentId, ?string $semester = null): float
    {
        $gradesQuery = Grade::with('course')
            ->where('student_id', $studentId);

        if ($semester) {
            $gradesQuery->where('semester', $semester);
        }

        $grades = $gradesQuery->get();

        if ($grades->isEmpty()) {
            return 0.0;
        }

        return $this->computeWeightedAverageFromGrades($grades);
    }

    /**
     * Compute weighted average from a collection of Grade models.
     * Weights each grade evaluation by its own coefficient.
     *
     * Formula: Sum(Grade_Note * Grade_Coefficient) / Sum(Grade_Coefficients)
     *
     * @param  Collection $grades  Collection of Grade models
     * @return float
     */
    public function computeWeightedAverageFromGrades(Collection $grades): float
    {
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $weightedSum    = 0.0;
        $coefficientSum = 0.0;

        foreach ($grades as $grade) {
            $coeff = (float) ($grade->coefficient ?? 0);
            if ($coeff <= 0) {
                $coeff = (float) ($grade->course?->coefficient ?? 1.0);
            }
            if ($coeff <= 0) {
                $coeff = 1.0;
            }

            $weightedSum    += (float) $grade->note * $coeff;
            $coefficientSum += $coeff;
        }

        if ($coefficientSum <= 0) {
            return 0.0;
        }

        return round($weightedSum / $coefficientSum, 2);
    }

    /**
     * Build a detailed subject breakdown for a student in a given semester.
     *
     * Returns an array of subject data including:
     *  - subject_name
     *  - coefficient
     *  - subject_average (weighted by evaluations' coefficients)
     *  - is_passing
     *  - grades (list of individual evaluations)
     *
     * @param  int         $studentId
     * @param  string|null $semester
     * @return array
     */
    public function getSubjectBreakdown(int $studentId, ?string $semester = null): array
    {
        $gradesQuery = Grade::with('course')
            ->where('student_id', $studentId);

        if ($semester) {
            $gradesQuery->where('semester', $semester);
        }

        $grades = $gradesQuery->get();
        $subjects = [];

        foreach ($grades->groupBy(fn($g) => $g->course_id ?? ('s:' . $g->subject_name)) as $key => $courseGrades) {
            $subjectWeightedSum = 0.0;
            $subjectCoeffSum    = 0.0;

            foreach ($courseGrades as $g) {
                $c = (float) ($g->coefficient ?? 0);
                if ($c <= 0) {
                    $c = (float) ($g->course?->coefficient ?? 1.0);
                }
                if ($c <= 0) {
                    $c = 1.0;
                }
                $subjectWeightedSum += (float) $g->note * $c;
                $subjectCoeffSum    += $c;
            }

            $subjectAvg = $subjectCoeffSum > 0 ? $subjectWeightedSum / $subjectCoeffSum : 0.0;
            $firstGrade = $courseGrades->first();
            $course     = $firstGrade?->course;

            $subjectName = $course?->title
                ?? $firstGrade->subject_name
                ?? 'Matière Inconnue';

            $individualGrades = $courseGrades->map(fn($g) => [
                'id'          => (int) $g->id,
                'note'        => (float) $g->note,
                'type'        => (string) ($g->type ?? ''),
                'coefficient' => (float) ($g->coefficient ?? 1.0),
                'exam_date'   => $g->exam_date ? (string) $g->exam_date : null,
                'comment'     => $g->comment,
            ])->values()->all();

            $subjects[] = [
                'course_id'       => $firstGrade->course_id,
                'subject_name'    => $subjectName,
                'coefficient'     => $subjectCoeffSum,
                'subject_average' => round($subjectAvg, 2),
                'is_passing'      => $subjectAvg >= 10,
                'grades'          => $individualGrades,
            ];
        }

        // Sort by subject name
        usort($subjects, fn($a, $b) => strcmp($a['subject_name'], $b['subject_name']));

        return $subjects;
    }

    /**
     * Compute full bulletin data for a student, including:
     *  - weighted general average per semester
     *  - subject breakdown per semester
     *  - rank in class (Rang)
     *  - mention (appreciation)
     *
     * @param  Student $student
     * @param  int     $classeId
     * @param  string  $semester
     * @return array
     */
    public function buildBulletinData(Student $student, int $classeId, string $semester): array
    {
        $breakdown      = $this->getSubjectBreakdown($student->idStudent, $semester);
        $weightedAvg    = $this->calculateWeightedAverage($student->idStudent, $semester);
        $totalCoeff     = array_sum(array_column($breakdown, 'coefficient'));
        $rank           = $this->calculateRankInClass($student->idStudent, $classeId, $semester);
        $mention        = $this->getMention($weightedAvg);

        return [
            'student'          => $student,
            'semester'         => $semester,
            'subjects'         => $breakdown,
            'total_coefficient'=> $totalCoeff,
            'weighted_average' => $weightedAvg,
            'rank'             => $rank,
            'mention'          => $mention,
        ];
    }

    /**
     * Calculate the class rank (Rang) for a specific student in a given semester.
     * Ranks students from highest weighted average to lowest.
     *
     * @param  int    $studentId
     * @param  int    $classeId
     * @param  string $semester
     * @return int|null  Rank (1 = top), or null if not determinable
     */
    public function calculateRankInClass(int $studentId, int $classeId, string $semester): ?int
    {
        // Get all students in the same class
        $classStudents = Student::whereHas('registres', function ($q) use ($classeId) {
            $q->where('Cla_id', $classeId);
        })->pluck('idStudent')->toArray();

        if (empty($classStudents)) {
            return null;
        }

        // Compute weighted average for each classmate
        $averages = [];
        foreach ($classStudents as $sid) {
            $gradesOfStudent = Grade::with('course')
                ->where('student_id', $sid)
                ->where('semester', $semester)
                ->get();

            $averages[$sid] = $gradesOfStudent->isEmpty()
                ? 0.0
                : $this->computeWeightedAverageFromGrades($gradesOfStudent);
        }

        // Sort descending
        arsort($averages);

        $rank = 1;
        foreach ($averages as $sid => $avg) {
            if ((int) $sid === (int) $studentId) {
                return $rank;
            }
            $rank++;
        }

        return null;
    }

    /**
     * Get the mention (appreciation) for a given average score.
     *
     * Scale:
     * - Moins de 10/20 → Ajourné
     * - 10.00 – 11.99  → Passable
     * - 12.00 – 13.99  → Assez Bien
     * - 14.00 – 15.99  → Bien
     * - 16.00 – 17.99  → Très Bien
     * - 18.00 – 20.00  → Excellent
     *
     * @param  float $average
     * @return string
     */
    public function getMention(float $average): string
    {
        if ($average >= 18.0) return 'Excellent';
        if ($average >= 16.0) return 'Très Bien';
        if ($average >= 14.0) return 'Bien';
        if ($average >= 12.0) return 'Assez Bien';
        if ($average >= 10.0) return 'Passable';
        return 'Ajourné';
    }

    /**
     * Get mention color for Filament/UI display.
     *
     * @param  float $average
     * @return string
     */
    public function getMentionColor(float $average): string
    {
        if ($average >= 18.0) return 'success';
        if ($average >= 16.0) return 'success';
        if ($average >= 14.0) return 'info';
        if ($average >= 12.0) return 'primary';
        if ($average >= 10.0) return 'warning';
        return 'danger';
    }
}
