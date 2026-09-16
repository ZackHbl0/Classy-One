<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Absence;
use App\Models\Planning;
use App\Models\Course;
use App\Http\Requests\ProfessorLoginRequest;
use App\Http\Requests\EnterGradeRequest;
use App\Http\Requests\MarkAbsenceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfessorController extends Controller
{
    /**
     * Handle Professor login.
     */
    public function login(ProfessorLoginRequest $request)
    {
        $validated = $request->validated();

        $professor = User::where('email', $validated['email'])
            ->whereIn('role', ['professeur', 'prof'])
            ->with('classes') // Eager load assigned classes
            ->first();

        if (!$professor || !Hash::check($validated['password'], $professor->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        // Revoke old tokens
        $professor->tokens()->delete();

        // Generate new token with role:professor scope
        $token = $professor->createToken('prof_auth_token', ['role:professor'])->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Connexion réussie',
            'token' => $token,
            'professor' => $professor
        ]);
    }

    /**
     * Handle Professor logout.
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie'
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
    public function enterGrade(EnterGradeRequest $request)
    {
        $professor = $request->user();
        $validated = $request->validated();

        $grade = Grade::create([
            'student_id' => $validated['student_id'],
            'teacher_id' => $professor->id,
            'course_id' => $validated['course_id'],
            'classe_id' => $validated['classe_id'],
            'note' => $validated['note'],
            'type' => $validated['type'],
            'subject_name' => $validated['subject_name'],
            'exam_date' => $validated['exam_date'],
            'comment' => $validated['comment'] ?? null,
            'semester' => $validated['semester'],
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
    public function markAbsence(MarkAbsenceRequest $request)
    {
        $professor = $request->user();
        $validated = $request->validated();

        $absence = Absence::create([
            'student_id' => $validated['student_id'],
            'classe_id' => $validated['classe_id'],
            'prof_id' => $professor->id,
            'matiere' => $validated['matiere'],
            'date' => $validated['date'],
            'seance' => $validated['seance'],
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
