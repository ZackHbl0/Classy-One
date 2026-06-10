<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    /**
     * Get all grades for the authenticated student.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $student = $request->user();

        // Fetch all grades for this student with relationships
        $grades = Grade::with(['course', 'teacher'])
            ->where('student_id', $student->idStudent)
            ->orderBy('exam_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($grades->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'statistics' => [
                    'total_grades' => 0,
                    'average' => 0,
                    'passing_rate' => 0,
                    'highest_grade' => 0,
                    'lowest_grade' => 0,
                ],
                'grouped_by_course' => [],
                'grouped_by_type' => [],
            ]);
        }

        // Map grades for mobile app
        $mapped = $grades->map(function ($grade) {
            return [
                'id' => (int) $grade->id,
                'subject_name' => $grade->subject_name ?? $grade->course->title,
                'note' => (float) $grade->note,
                'note_formatted' => number_format($grade->note, 2),
                'type' => $grade->type,
                'status' => $grade->status,
                'color' => $grade->color,
                'is_passing' => $grade->isPassing(),
                'exam_date' => $grade->exam_date ? $grade->exam_date->format('Y-m-d') : null,
                'exam_date_formatted' => $grade->exam_date ? $grade->exam_date->format('d/m/Y') : null,
                'semester' => $grade->semester,
                'comment' => $grade->comment,
                'teacher_name' => $grade->teacher ? $grade->teacher->name : 'Professeur',
                'created_at' => $grade->created_at ? $grade->created_at->toIso8601String() : null,
            ];
        });

        // Calculate statistics
        $totalGrades = $grades->count();
        $average = $grades->avg('note');
        $passingCount = $grades->filter(fn($g) => $g->note >= 10)->count();
        $passingRate = $totalGrades > 0 ? ($passingCount / $totalGrades) * 100 : 0;
        $highestGrade = $grades->max('note');
        $lowestGrade = $grades->min('note');

        $statistics = [
            'total_grades' => $totalGrades,
            'average' => round($average, 2),
            'average_formatted' => number_format($average, 2),
            'passing_rate' => round($passingRate, 2),
            'passing_rate_formatted' => number_format($passingRate, 1) . '%',
            'highest_grade' => (float) $highestGrade,
            'lowest_grade' => (float) $lowestGrade,
        ];

        // Group by course/subject
        $groupedByCourse = $grades->groupBy(fn($g) => $g->subject_name ?? $g->course->title)
            ->map(function ($courseGrades, $subjectName) {
                $avg = $courseGrades->avg('note');
                return [
                    'subject_name' => $subjectName,
                    'total_grades' => $courseGrades->count(),
                    'average' => round($avg, 2),
                    'average_formatted' => number_format($avg, 2),
                    'grades' => $courseGrades->map(function ($g) {
                        return [
                            'id' => (int) $g->id,
                            'note' => (float) $g->note,
                            'type' => $g->type,
                            'status' => $g->status,
                            'exam_date' => $g->exam_date ? $g->exam_date->format('d/m/Y') : null,
                        ];
                    })->values(),
                ];
            })->values();

        // Group by type (Contrôle 1, Contrôle 2, etc.)
        $groupedByType = $grades->groupBy('type')
            ->map(function ($typeGrades, $type) {
                $avg = $typeGrades->avg('note');
                return [
                    'type' => $type,
                    'total_grades' => $typeGrades->count(),
                    'average' => round($avg, 2),
                    'average_formatted' => number_format($avg, 2),
                    'grades' => $typeGrades->map(function ($g) {
                        return [
                            'id' => (int) $g->id,
                            'subject_name' => $g->subject_name ?? $g->course->title,
                            'note' => (float) $g->note,
                            'status' => $g->status,
                            'exam_date' => $g->exam_date ? $g->exam_date->format('d/m/Y') : null,
                        ];
                    })->values(),
                ];
            })->values();

        // Group by semester (if available)
        $groupedBySemester = $grades->whereNotNull('semester')
            ->groupBy('semester')
            ->map(function ($semesterGrades, $semester) {
                $avg = $semesterGrades->avg('note');
                return [
                    'semester' => $semester,
                    'total_grades' => $semesterGrades->count(),
                    'average' => round($avg, 2),
                    'average_formatted' => number_format($avg, 2),
                ];
            })->values();

        return response()->json([
            'success' => true,
            'data' => $mapped,
            'statistics' => $statistics,
            'grouped_by_course' => $groupedByCourse,
            'grouped_by_type' => $groupedByType,
            'grouped_by_semester' => $groupedBySemester,
        ]);
    }

    /**
     * Get grade statistics for the authenticated student.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $student = $request->user();

        $stats = Grade::where('student_id', $student->idStudent)
            ->selectRaw('
                COUNT(*) as total_grades,
                AVG(note) as average,
                MAX(note) as highest,
                MIN(note) as lowest,
                SUM(CASE WHEN note >= 10 THEN 1 ELSE 0 END) as passing_count
            ')
            ->first();

        $passingRate = $stats->total_grades > 0
            ? ($stats->passing_count / $stats->total_grades) * 100
            : 0;

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_grades' => (int) $stats->total_grades,
                'average' => round($stats->average, 2),
                'highest_grade' => (float) $stats->highest,
                'lowest_grade' => (float) $stats->lowest,
                'passing_count' => (int) $stats->passing_count,
                'failing_count' => (int) ($stats->total_grades - $stats->passing_count),
                'passing_rate' => round($passingRate, 2),
            ],
        ]);
    }

    /**
     * Get grades grouped by course/subject.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function byCourse(Request $request)
    {
        $student = $request->user();

        $grades = Grade::with(['course'])
            ->where('student_id', $student->idStudent)
            ->get();

        $grouped = $grades->groupBy(fn($g) => $g->subject_name ?? $g->course->title)
            ->map(function ($courseGrades, $subjectName) {
                $avg = $courseGrades->avg('note');
                return [
                    'subject_name' => $subjectName,
                    'total_grades' => $courseGrades->count(),
                    'average' => round($avg, 2),
                    'average_formatted' => number_format($avg, 2),
                    'highest' => (float) $courseGrades->max('note'),
                    'lowest' => (float) $courseGrades->min('note'),
                    'grades' => $courseGrades->map(function ($g) {
                        return [
                            'id' => (int) $g->id,
                            'note' => (float) $g->note,
                            'type' => $g->type,
                            'status' => $g->status,
                            'color' => $g->color,
                            'exam_date' => $g->exam_date ? $g->exam_date->format('d/m/Y') : null,
                            'comment' => $g->comment,
                        ];
                    })->values(),
                ];
            })->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * Get grades grouped by exam type.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function byType(Request $request)
    {
        $student = $request->user();

        $grades = Grade::with(['course'])
            ->where('student_id', $student->idStudent)
            ->get();

        $grouped = $grades->groupBy('type')
            ->map(function ($typeGrades, $type) {
                $avg = $typeGrades->avg('note');
                return [
                    'type' => $type,
                    'total_grades' => $typeGrades->count(),
                    'average' => round($avg, 2),
                    'average_formatted' => number_format($avg, 2),
                    'grades' => $typeGrades->map(function ($g) {
                        return [
                            'id' => (int) $g->id,
                            'subject_name' => $g->subject_name ?? $g->course->title,
                            'note' => (float) $g->note,
                            'status' => $g->status,
                            'color' => $g->color,
                            'exam_date' => $g->exam_date ? $g->exam_date->format('d/m/Y') : null,
                        ];
                    })->values(),
                ];
            })->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }
}
