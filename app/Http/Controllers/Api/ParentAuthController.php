<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolParent;
use App\Http\Requests\ParentLoginRequest;
use App\Http\Requests\UpdateFcmTokenRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentAuthController extends Controller
{
    /**
     * Handle parent login.
     */
    public function login(ParentLoginRequest $request)
    {
        $validated = $request->validated();
        $loginInput = trim($validated['email']);

        // Find parent by email or phone
        $parent = SchoolParent::where('email', $loginInput)
            ->orWhere('phone', $loginInput)
            ->with(['students.registres.classe'])
            ->first();

        if (!$parent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Identifiant ou mot de passe incorrect.',
            ], 401);
        }

        // Verify password
        $passwordMatches = false;
        if (Hash::check($validated['password'], $parent->password)) {
            $passwordMatches = true;
        } elseif ($parent->password === $validated['password']) {
            // Graceful upgrade if plain text
            $parent->password = Hash::make($validated['password']);
            $parent->save();
            $passwordMatches = true;
        }

        if (!$passwordMatches) {
            return response()->json([
                'status' => 'error',
                'message' => 'Identifiant ou mot de passe incorrect.',
            ], 401);
        }

        // Revoke previous tokens
        $parent->tokens()->delete();

        // Save new FCM token if provided
        if (!empty($validated['fcmToken'])) {
            $parent->update(['fcm_token' => $validated['fcmToken']]);
        }

        // Create new Sanctum personal access token with role:parent scope
        $token = $parent->createToken('parent_auth_token', ['role:parent'])->plainTextToken;

        // Map children for instant client reference
        $children = $parent->students->map(function ($student) {
            $registre = $student->registres->first();
            return [
                'idStudent' => $student->idStudent,
                'matricule' => $student->matricule,
                'nom' => $student->nom,
                'prenom' => $student->prenom,
                'nom_complet' => $student->nom . ' ' . $student->prenom,
                'classe' => $registre?->classe?->nomClasse ?? 'Non assignée',
                'telephone' => $student->telephone,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Connexion réussie',
            'token' => $token,
            'parent' => [
                'id' => $parent->id,
                'name' => $parent->name,
                'email' => $parent->email,
                'phone' => $parent->phone,
            ],
            'children' => $children,
        ]);
    }

    /**
     * Update parent FCM push notification token.
     */
    public function updateFcmToken(UpdateFcmTokenRequest $request)
    {
        $validated = $request->validated();
        $parent = $request->user();

        if ($parent) {
            $parent->update(['fcm_token' => $validated['fcmToken']]);
            return response()->json([
                'status' => 'success',
                'message' => 'Token FCM parent mis à jour.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Non authentifié.',
        ], 401);
    }

    /**
     * Handle parent logout.
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->update(['fcm_token' => null]);
            if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie',
        ]);
    }
}
