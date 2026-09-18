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
            return response()->json([
                'status' => 'error',
                'message' => 'Matricule ou mot de passe incorrect'
            ], 401);
        }

        // Check if the password is valid
        $passwordMatches = false;

        // 1. Check plain text match first (safe against non-bcrypt values in DB)
        if ($student->password === $validated['password'] || ($student->password_plain && $student->password_plain === $validated['password'])) {
            $passwordMatches = true;
            $student->password_plain = $validated['password'];

            // Automatically upgrade plain-text password to bcrypt for future logins
            if (!empty($student->password) && !str_starts_with($student->password, '$2y$') && !str_starts_with($student->password, '$2a$')) {
                $student->password = Hash::make($validated['password']);
            }
            $student->save();
        }
        // 2. Fallback: Check bcrypt hash using native password_verify (never throws exception)
        else if (!empty($student->password) && (str_starts_with($student->password, '$2y$') || str_starts_with($student->password, '$2a$'))) {
            if (password_verify($validated['password'], $student->password)) {
                $passwordMatches = true;
                if (empty($student->password_plain)) {
                    $student->password_plain = $validated['password'];
                    $student->save();
                }
            }
        }

        if (!$passwordMatches) {
            return response()->json([
                'status' => 'error',
                'message' => 'Matricule ou mot de passe incorrect'
            ], 401);
        }

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
            'password_plain' => $validated['password'],
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
