<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PasswordResetCode;
use App\Models\SchoolParent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Request a password reset OTP code using matricule, email, or telephone.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|min:3',
        ]);

        $identifier = trim($request->input('identifier'));
        $user = null;
        $userType = null;
        $maskedContact = null;

        // 1. Try finding Student by matricule or telephone
        $student = Student::where('matricule', $identifier)
            ->orWhere('telephone', $identifier)
            ->first();

        if ($student) {
            $user = $student;
            $userType = 'student';
            $contact = $student->telephone ?: $student->matricule;
            $maskedContact = $this->maskContact($contact);
        }

        // 2. Try finding SchoolParent by email or phone
        if (!$user) {
            $parent = SchoolParent::where('email', $identifier)
                ->orWhere('phone', $identifier)
                ->first();

            if ($parent) {
                $user = $parent;
                $userType = 'parent';
                $contact = $parent->email ?: $parent->phone;
                $maskedContact = $this->maskContact($contact);
            }
        }

        // 3. Try finding User (Admin / Professor) by email or matricule
        if (!$user) {
            $staff = User::where('email', $identifier)
                ->orWhere('matricule', $identifier)
                ->first();

            if ($staff) {
                $user = $staff;
                $userType = 'user';
                $contact = $staff->email ?: $staff->matricule;
                $maskedContact = $this->maskContact($contact);
            }
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Aucun compte associé à cet identifiant (matricule, email ou téléphone).',
            ], 404);
        }

        // Invalidate any previous unexpired codes for this identifier
        PasswordResetCode::where('identifier', $identifier)
            ->whereNull('used_at')
            ->delete();

        // Generate 6-digit OTP and unique token
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);

        $resetCode = PasswordResetCode::create([
            'identifier' => $identifier,
            'code' => $code,
            'token' => $token,
            'user_type' => $userType,
            'user_id' => $user->getKey(),
            'expires_at' => now()->addMinutes(15),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => $userType === 'user' ? $user->id : null,
            'user_name' => $user->name ?? $user->nom ?? $identifier,
            'user_role' => $userType,
            'action' => 'password_reset_requested',
            'subject_type' => get_class($user),
            'subject_id' => $user->getKey(),
            'description' => "Demande de réinitialisation de mot de passe pour [{$identifier}].",
            'ip_address' => $request->ip(),
            'details' => [
                'identifier' => $identifier,
                'user_type' => $userType,
                'expires_at' => $resetCode->expires_at->toIso8601String(),
            ],
        ]);

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Un code de vérification à 6 chiffres a été généré avec succès (valide 15 minutes).',
            'identifier' => $identifier,
            'masked_contact' => $maskedContact,
            'debug_code' => app()->environment(['local', 'testing']) ? $code : null,
        ]);
    }

    /**
     * Verify the 6-digit OTP code and exchange for a valid reset token.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $identifier = trim($request->input('identifier'));
        $code = trim($request->input('code'));

        $record = PasswordResetCode::where('identifier', $identifier)
            ->where('code', $code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Code de vérification incorrect ou expiré. Veuillez réessayer.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Code vérifié avec succès.',
            'reset_token' => $record->token,
        ]);
    }

    /**
     * Reset the user password using verified reset token.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $identifier = trim($request->input('identifier'));
        $token = trim($request->input('reset_token'));

        $record = PasswordResetCode::where('identifier', $identifier)
            ->where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Jeton de réinitialisation invalide ou expiré. Veuillez recommencer la procédure.',
            ], 422);
        }

        // Find the user to update
        $user = match ($record->user_type) {
            'student' => Student::find($record->user_id),
            'parent' => SchoolParent::find($record->user_id),
            'user' => User::find($record->user_id),
            default => null,
        };

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        // Update password with secure bcrypt hash
        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Mark code as used
        $record->update(['used_at' => now()]);

        // Revoke all existing tokens for enhanced security
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // Audit log
        AuditLog::create([
            'user_id' => $record->user_type === 'user' ? $user->id : null,
            'user_name' => $user->name ?? $user->nom ?? $identifier,
            'user_role' => $record->user_type,
            'action' => 'password_reset_completed',
            'subject_type' => get_class($user),
            'subject_id' => $user->getKey(),
            'description' => "Mot de passe réinitialisé avec succès pour [{$identifier}].",
            'ip_address' => $request->ip(),
            'details' => [
                'identifier' => $identifier,
                'user_type' => $record->user_type,
                'reset_at' => now()->toIso8601String(),
            ],
        ]);

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.',
        ]);
    }

    /**
     * Helper to mask phone numbers or emails for safe UI preview.
     */
    protected function maskContact(string $contact): string
    {
        if (str_contains($contact, '@')) {
            $parts = explode('@', $contact);
            $name = $parts[0];
            $domain = $parts[1] ?? '';
            $maskedName = strlen($name) > 2 ? substr($name, 0, 2) . str_repeat('*', strlen($name) - 2) : $name;
            return $maskedName . '@' . $domain;
        }

        if (strlen($contact) >= 6) {
            return substr($contact, 0, 2) . str_repeat('*', strlen($contact) - 4) . substr($contact, -2);
        }

        return $contact;
    }
}
