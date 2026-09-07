<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Absence;
use App\Models\Planning;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfessorController extends Controller
{
    /**
     * Handle Professor login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid inputs.',
                'errors' => $validator->errors()
            ], 422);
        }

        $professor = User::where('email', $request->email)
            ->whereIn('role', ['professeur', 'prof'])
            ->with('classes') // Eager load assigned classes
            ->first();

        if (!$professor || !Hash::check($request->password, $professor->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        // Revoke old tokens
        $professor->tokens()->delete();

        // Generate new token
        $token = $professor->createToken('prof_auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Connexion réussie',
            'token' => $token,
            'professor' => $professor
        ]);
    }

    /**
     * Get students assigned to the professor.
     */
    public function getStudents(Request $request)
    {
        $professor = $request->user();

        // Get class IDs for the professor from the Many-to-Many pivot
        $classIds = $professor->classes->pluck('id')->toArray();

        // Include classes from courses taught as fallback
        $courseClassIds = Course::where('professor_id', $professor->id)->pluck('classe_id')->toArray();
        
        $allClassIds = collect($classIds)->merge($courseClassIds)->filter()->unique()->toArray();

        $students = Student::whereHas('registres', function ($q) use ($allClassIds) {
            $q->whereIn('Cla_id', $allClassIds);
        })->with('classe')->get();

        return response()->json([
            'status' => 'success',
            'data' => $students
        ]);
    }

    /**
     * Get professor schedules.
     */
    public function getSchedules(Request $request)
    {
        $professor = $request->user();

        // Get class IDs for the professor
        $classIds = $professor->classes->pluck('id')->toArray();

        // Fetch plannings linked to these classes
        // Note: Planning table has classe_id
        $schedules = Planning::whereIn('classe_id', $classIds)
            ->with(['classe', 'matiere'])
            ->orderBy('jour')
            ->orderBy('heure_debut')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $schedules
        ]);
    }

    /**
     * Enter a grade for a student.
     */
    public function enterGrade(Request $request)
    {
        $professor = $request->user();

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:student,idStudent',
            'course_id' => 'required|exists:courses,id',
            'classe_id' => 'required|exists:classe,id',
            'note' => 'required|numeric|min:0|max:20',
            'type' => 'required|string',
            'subject_name' => 'required|string',
            'exam_date' => 'required|date',
            'comment' => 'nullable|string',
            'semester' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $grade = Grade::create([
            'student_id' => $request->student_id,
            'teacher_id' => $professor->id,
            'course_id' => $request->course_id,
            'classe_id' => $request->classe_id,
            'note' => $request->note,
            'type' => $request->type,
            'subject_name' => $request->subject_name,
            'exam_date' => $request->exam_date,
            'comment' => $request->comment,
            'semester' => $request->semester,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Note ajoutée avec succès',
            'data' => $grade
        ], 201);
    }

    /**
     * Mark an absence for a student.
     */
    public function markAbsence(Request $request)
    {
        $professor = $request->user();

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:student,idStudent',
            'classe_id' => 'required|exists:classe,id',
            'matiere' => 'required|string',
            'date' => 'required|date',
            'seance' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $absence = Absence::create([
            'student_id' => $request->student_id,
            'classe_id' => $request->classe_id,
            'prof_id' => $professor->id,
            'matiere' => $request->matiere,
            'date' => $request->date,
            'seance' => $request->seance,
            'is_justified' => false,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Absence enregistrée avec succès',
            'data' => $absence
        ], 201);
    }
}
