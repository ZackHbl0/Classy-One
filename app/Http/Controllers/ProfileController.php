<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdatePhoneRequest;
use App\Http\Requests\UpdateFcmTokenRequest;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $student = $request->user();

        $validated = $request->validated();

        // Verify old password (check bcrypt hash, fallback to plain-text check for unmigrated accounts)
        $passwordMatches = false;
        if (Hash::check($validated['currentPassword'], $student->password)) {
            $passwordMatches = true;
        } else if ($student->password === $validated['currentPassword']) {
            $passwordMatches = true;
        }

        if (!$passwordMatches) {
            return response()->json(["success" => false, "message" => "Mot de passe actuel incorrect."]);
        }

        // Update with new bcrypt hash securely
        $student->password = Hash::make($validated['newPassword']);
        $student->save();

        // Revoke all other Sanctum tokens upon password change
        if (method_exists($student, 'tokens')) {
            $currentTokenId = $request->user()->currentAccessToken()?->id;
            if ($currentTokenId) {
                $student->tokens()->where('id', '!=', $currentTokenId)->delete();
            } else {
                $student->tokens()->delete();
            }
        }

        \App\Models\AuditLog::create([
            'user_id' => ($student instanceof \App\Models\User) ? $student->id : null,
            'user_name' => $student->prenom . ' ' . $student->nom,
            'user_role' => 'student',
            'action' => 'password_changed',
            'subject_type' => get_class($student),
            'subject_id' => (string) $student->getKey(),
            'description' => 'Changement de mot de passe du compte et révocation des autres sessions.',
            'ip_address' => $request->ip(),
            'details' => ['revoked_other_tokens' => true],
        ]);

        return response()->json(["success" => true, "message" => "Mot de passe mis à jour avec succès. Toutes vos autres sessions ont été déconnectées."]);
    }

    public function updatePhone(UpdatePhoneRequest $request)
    {
        $student = $request->user();

        $validated = $request->validated();

        $student->telephone = $validated['newPhone'];
        $student->save();

        return response()->json(["success" => true, "message" => "Téléphone mis à jour."]);
    }

    public function updateFcmToken(UpdateFcmTokenRequest $request)
    {
        $student = $request->user();

        $validated = $request->validated();

        $student->fcmToken = $validated['fcmToken'];
        $student->save();

        return response()->json(["success" => true, "message" => "Token FCM mis à jour."]);
    }

    public function updatePreferences(Request $request)
    {
        $student = $request->user();

        $validator = Validator::make($request->all(), [
            'eventNotifications' => 'required|boolean',
            'paymentNotifications' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => "Données invalides."]);
        }

        $student->event_notifications = $request->eventNotifications;
        $student->payment_notifications = $request->paymentNotifications;
        $student->save();

        return response()->json(["success" => true, "message" => "Préférences mises à jour."]);
    }
}
