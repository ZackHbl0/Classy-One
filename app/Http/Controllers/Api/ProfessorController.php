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

use App\Models\Registre;
use App\Models\Classe;

class ProfessorController extends Controller
{
    /**
     * Handle Professor login.
     */
    public function login(ProfessorLoginRequest $request)
    {
        $validated = $request->validated();

        $login = trim($validated['matricule'] ?? $validated['email'] ?? '');

        $professor = User::where(function ($query) use ($login) {
                $query->where('matricule', $login)
                      ->orWhere('matricule', strtoupper($login))
                      ->orWhere('email', $login);
            })
            ->whereIn('role', ['professeur', 'prof'])
            ->with('classes') // Eager load assigned classes
            ->first();

        if (!$professor || !Hash::check($validated['password'], $professor->password)) {
            app(\App\Services\SuspiciousActivityService::class)->handleFailedLogin($login, $request->ip(), 'professor');
            return response()->json([
                'status' => 'error',
                'message' => 'Matricule ou mot de passe incorrect'
            ], 401);
        }

        // Reset failed login counter on success
        app(\App\Services\SuspiciousActivityService::class)->handleSuccessfulLogin($login, $request->ip());

        // Check if 2FA is enabled for this professor
        if ($professor->hasTwoFactorEnabled()) {
            $preAuthToken = \Illuminate\Support\Str::random(64);
            cache()->put("2fa_preauth_{$preAuthToken}", [
                'user_id' => $professor->id,
                'role' => $professor->role,
                'ip' => $request->ip(),
            ], now()->addMinutes(5));

            return response()->json([
                'status' => '2fa_required',
                'two_factor_required' => true,
                'pre_auth_token' => $preAuthToken,
                'message' => 'Authentification à deux facteurs requise. Veuillez valider votre code via /api/2fa/verify.',
            ], 200);
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
     * Get all class IDs assigned to the professor.
     */
    protected function getAllowedClassIds(User $professor): array
    {
        $pivotClassIds = $professor->classes()->pluck('classe.id')->toArray();
        $courseClassIds = Course::where('professor_id', $professor->id)->pluck('classe_id')->toArray();
        $planningClassIds = Planning::where(function ($q) use ($professor) {
            $q->where('professeur_name', $professor->name)
              ->orWhere('professeur_name', 'like', '%' . $professor->name . '%');
        })->pluck('classe_id')->toArray();
        $directClassId = $professor->classe_id ? [$professor->classe_id] : [];

        return collect($pivotClassIds)
            ->merge($courseClassIds)
            ->merge($planningClassIds)
            ->merge($directClassId)
            ->filter()
            ->unique()
            ->map(fn($id) => (int)$id)
            ->values()
            ->toArray();
    }

    /**
     * Verify whether a professor is authorized to teach/grade a given subject.
     */
    protected function isSubjectAllowed(User $professor, string $subjectName, ?int $courseId = null): bool
    {
        // If course ID provided and belongs directly to this professor
        if ($courseId && Course::where('id', $courseId)->where('professor_id', $professor->id)->exists()) {
            return true;
        }

        $configuredMatieres = collect($professor->matieres ?? []);
        $courseSubjects = Course::where('professor_id', $professor->id)->pluck('title');
        $planningSubjects = Planning::where(function ($q) use ($professor) {
            $q->where('professeur_name', $professor->name)
              ->orWhere('professeur_name', 'like', '%' . $professor->name . '%');
        })->pluck('matiere');

        $allSubjects = $configuredMatieres->merge($courseSubjects)->merge($planningSubjects)
            ->map(fn($s) => mb_strtolower(trim((string)$s)))
            ->filter()
            ->unique();

        // If no specific subject restriction configured, allow default
        if ($allSubjects->isEmpty()) {
            return true;
        }

        $needle = mb_strtolower(trim($subjectName));
        return $allSubjects->contains(function ($s) use ($needle) {
            return $s === $needle || str_contains($needle, $s) || str_contains($s, $needle);
        });
    }

    /**
     * Get students strictly assigned to the professor's classes.
     */
    public function getStudents(Request $request)
    {
        $professor = $request->user();
        $allowedClassIds = $this->getAllowedClassIds($professor);

        if (empty($allowedClassIds)) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        $query = Student::whereHas('registres', function ($q) use ($allowedClassIds) {
            $q->whereIn('Cla_id', $allowedClassIds);
        })->with('classe');

        // Optional filter by class ID with strict ownership validation
        if ($request->filled('classe_id')) {
            $requestedClasseId = (int) $request->classe_id;
            if (!in_array($requestedClasseId, $allowedClassIds, true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Accès refusé. Vous n\'êtes pas assigné à cette classe.'
                ], 403);
            }
            $query->whereHas('registres', fn($q) => $q->where('Cla_id', $requestedClasseId));
        }

        $students = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $students
        ]);
    }

    /**
     * Get professor schedules restricted to their assigned classes & sessions.
     */
    public function getSchedules(Request $request)
    {
        $professor = $request->user();
        $allowedClassIds = $this->getAllowedClassIds($professor);

        if (empty($allowedClassIds)) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        $schedules = Planning::whereIn('classe_id', $allowedClassIds)
            ->where(function ($q) use ($professor) {
                $q->whereNull('professeur_name')
                  ->orWhere('professeur_name', '')
                  ->orWhere('professeur_name', $professor->name)
                  ->orWhere('professeur_name', 'like', '%' . $professor->name . '%');
            })
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
     * Enter a grade for a student with strict class, student, and subject authorization.
     */
    public function enterGrade(EnterGradeRequest $request)
    {
        $professor = $request->user();
        $validated = $request->validated();
        $classeId = (int) $validated['classe_id'];
        $studentId = (int) $validated['student_id'];

        // 1. Enforce class assignment check
        $allowedClassIds = $this->getAllowedClassIds($professor);
        if (!in_array($classeId, $allowedClassIds, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès refusé. Vous n\'êtes pas assigné à cette classe.'
            ], 403);
        }

        // 2. Enforce student enrollment check in this specific class
        $isEnrolled = Registre::where('Cla_id', $classeId)->where('idStudent', $studentId)->exists();
        if (!$isEnrolled) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès refusé. Cet élève n\'est pas inscrit dans cette classe.'
            ], 403);
        }

        // 3. Enforce subject/course assignment check
        $subjectName = $validated['subject_name'] ?? '';
        $courseId = !empty($validated['course_id']) ? (int) $validated['course_id'] : null;
        if (!$this->isSubjectAllowed($professor, $subjectName, $courseId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès refusé. La matière "' . $subjectName . '" ne vous est pas assignée.'
            ], 403);
        }

        $grade = Grade::create([
            'student_id' => $studentId,
            'teacher_id' => $professor->id,
            'course_id' => $courseId,
            'classe_id' => $classeId,
            'note' => $validated['note'],
            'type' => $validated['type'],
            'subject_name' => $subjectName,
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
     * Mark an absence for a student with strict class, student, and subject authorization.
     */
    public function markAbsence(MarkAbsenceRequest $request)
    {
        $professor = $request->user();
        $validated = $request->validated();
        $classeId = (int) $validated['classe_id'];
        $studentId = (int) $validated['student_id'];

        // 1. Enforce class assignment check
        $allowedClassIds = $this->getAllowedClassIds($professor);
        if (!in_array($classeId, $allowedClassIds, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès refusé. Vous n\'êtes pas assigné à cette classe.'
            ], 403);
        }

        // 2. Enforce student enrollment check in this specific class
        $isEnrolled = Registre::where('Cla_id', $classeId)->where('idStudent', $studentId)->exists();
        if (!$isEnrolled) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès refusé. Cet élève n\'est pas inscrit dans cette classe.'
            ], 403);
        }

        // 3. Enforce subject assignment check
        $matiere = $validated['matiere'] ?? '';
        if (!$this->isSubjectAllowed($professor, $matiere)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès refusé. La matière "' . $matiere . '" ne vous est pas assignée.'
            ], 403);
        }

        $absence = Absence::create([
            'student_id' => $studentId,
            'classe_id' => $classeId,
            'prof_id' => $professor->id,
            'matiere' => $matiere,
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
