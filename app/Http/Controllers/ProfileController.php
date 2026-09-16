<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdatePhoneRequest;
use App\Http\Requests\UpdateFcmTokenRequest;

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

        // Update with new bcrypt hash
        $student->password = Hash::make($validated['newPassword']);
        $student->save();

        return response()->json(["success" => true, "message" => "Mot de passe mis à jour avec succès."]);
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
