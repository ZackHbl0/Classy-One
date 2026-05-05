<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function updatePassword(Request $request)
    {
        $student = $request->user();

        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:6',
        ]);

        if ($validator->fails() || empty($request->currentPassword)) {
            return response()->json(["success" => false, "message" => "Paramètres incomplets ou invalides."]);
        }

        // Verify old password (check bcrypt hash, fallback to plain-text check for unmigrated accounts)
        $passwordMatches = false;
        if (Hash::check($request->currentPassword, $student->password)) {
            $passwordMatches = true;
        } else if ($student->password === $request->currentPassword) {
            $passwordMatches = true;
        }

        if (!$passwordMatches) {
            return response()->json(["success" => false, "message" => "Mot de passe actuel incorrect."]);
        }

        // Update with new bcrypt hash
        $student->password = Hash::make($request->newPassword);
        $student->save();

        return response()->json(["success" => true, "message" => "Mot de passe mis à jour avec succès."]);
    }

    public function updatePhone(Request $request)
    {
        $student = $request->user();

        $validator = Validator::make($request->all(), [
            'newPhone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => "Numéro de téléphone invalide."]);
        }

        $student->telephone = $request->newPhone;
        $student->save();

        return response()->json(["success" => true, "message" => "Téléphone mis à jour."]);
    }

    public function updateFcmToken(Request $request)
    {
        $student = $request->user();

        $validator = Validator::make($request->all(), [
            'fcmToken' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => "Token manquant."]);
        }

        $student->fcmToken = $request->fcmToken;
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
