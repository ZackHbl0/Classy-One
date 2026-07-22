<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle student login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'matricule' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid inputs.',
                'errors' => $validator->errors()
            ], 422);
        }

        $student = Student::where('matricule', $request->matricule)->first();

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Matricule ou mot de passe incorrect'
            ], 401);
        }

        // Check if the password is valid
        $passwordMatches = false;

        // 1. Check if it's already a bcrypt hash
        if (Hash::check($request->password, $student->password)) {
            $passwordMatches = true;
        }
        // 2. Fallback: Check if it's still plain text (graceful migration)
        else if ($student->password === $request->password) {
            $passwordMatches = true;

            // Automatically upgrade plain-text password to bcrypt for future logins
            $student->password = Hash::make($request->password);
            $student->save();
        }

        if (!$passwordMatches) {
            return response()->json([
                'status' => 'error',
                'message' => 'Matricule ou mot de passe incorrect'
            ], 401);
        }

        // Revoke all existing tokens for the user to prevent multiple sessions (optional but good practice)
        $student->tokens()->delete();

        // Save new FCM token if provided
        if ($request->has('fcmToken') && !empty($request->fcmToken)) {
            $student->update(['fcmToken' => $request->fcmToken]);
        }

        // Generate new Sanctum token
        $token = $student->createToken('auth_token')->plainTextToken;

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
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'matricule' => 'required|string|unique:student,matricule',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $student = Student::create([
            'matricule' => $request->matricule,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'password' => Hash::make($request->password), // Always hash new passwords
            'telephone' => $request->telephone,
            'fcmToken' => $request->fcmToken,
        ]);

        $token = $student->createToken('auth_token')->plainTextToken;

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
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie'
        ]);
    }
}
