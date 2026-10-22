<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle student login with strict validation and scoped token.
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $student = Student::where('matricule', $validated['matricule'])->first();

        if (!$student) {
            app(\App\Services\SuspiciousActivityService::class)->handleFailedLogin($validated['matricule'], $request->ip(), 'student');
            return response()->json([
                'status' => 'error',
                'message' => 'Matricule ou mot de passe incorrect'
            ], 401);
        }

        // Secure password verification with auto-rehash for legacy plain passwords
        $passwordMatches = false;

        if (password_verify($validated['password'], $student->password)) {
            $passwordMatches = true;
        } elseif ($student->password === $validated['password']) {
            // Legacy plain password detected: upgrade immediately to bcrypt
            $passwordMatches = true;
            $student->password = Hash::make($validated['password']);
            $student->save();
        }

        if (!$passwordMatches) {
            app(\App\Services\SuspiciousActivityService::class)->handleFailedLogin($validated['matricule'], $request->ip(), 'student');
            return response()->json([
                'status' => 'error',
                'message' => 'Matricule ou mot de passe incorrect'
            ], 401);
        }

        // Reset failed login counter on success
        app(\App\Services\SuspiciousActivityService::class)->handleSuccessfulLogin($validated['matricule'], $request->ip());

        // Revoke all existing tokens for the user to prevent multiple concurrent sessions
        $student->tokens()->delete();

        // Save new FCM token if provided
        if (!empty($validated['fcmToken'])) {
            $student->update(['fcmToken' => $validated['fcmToken']]);
        }

        // Generate scoped Sanctum token
        $token = $student->createToken('student_auth_token', ['role:student'])->plainTextToken;

        // Fetch and append class name
        $registre = \App\Models\Registre::with('classe')->where('idStudent', $student->idStudent)->first();
        if ($registre && $registre->classe) {
            $student->classe = $registre->classe->nomClasse;
        } else {
            $student->classe = '';
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Connexion réussie',
            'token' => $token,
            'student' => $student
        ]);
    }

    /**
     * Handle student registration.
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $student = Student::create([
            'matricule' => $validated['matricule'],
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'password' => Hash::make($validated['password']),
            'telephone' => $validated['telephone'] ?? null,
            'fcmToken' => $validated['fcmToken'] ?? null,
        ]);

        $token = $student->createToken('student_auth_token', ['role:student'])->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Inscription réussie',
            'token' => $token,
            'student' => $student
        ]);
    }

    /**
     * Handle student logout.
     */
    public function logout(Request $request)
    {
        if ($request->user() && method_exists($request->user(), 'currentAccessToken') && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie'
        ]);
    }
}
