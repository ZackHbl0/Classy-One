<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

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
            return [
                'id' => (int) $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'file_path' => $course->file_path,
                // absolute stream url/download url if file exists
                'video_url' => $course->file_path ? url(\Illuminate\Support\Facades\Storage::disk('public')->url($course->file_path)) : null,
                'professor_name' => $course->professor ? $course->professor->name : 'Professeur',
                'created_at' => $course->created_at ? $course->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mapped
        ]);
    }
}
