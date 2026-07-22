<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send a push notification to multiple targets based on target_type
     */
    public static function sendNotification($notification)
    {
        $messaging = app('firebase.messaging');
        $tokens = [];

        Log::info("FCM: Starting broadcast for Notification ID: {$notification->id} | Type: {$notification->target_type}");

        // 1. Resolve target students based on target_type
        $query = Student::query()->whereNotNull('fcmToken');

        switch ($notification->target_type) {
            case 'all':
                // No extra filters needed for 'all'
                break;

            case 'students':
                // Support both single idStudent field and multiple target_ids array
                $studentIds = $notification->target_ids ?? [];
                if ($notification->idStudent && !in_array($notification->idStudent, $studentIds)) {
                    $studentIds[] = $notification->idStudent;
                }
                $query->whereIn('idStudent', $studentIds);
                break;

            case 'classes':
                $classIds = $notification->target_ids;
                if (!empty($classIds)) {
                    $query->whereHas('registres', function ($q) use ($classIds) {
                        $q->whereIn('Cla_id', $classIds);
                    });
                } else {
                    Log::warning("FCM: Target type 'classes' but target_ids is empty for Notification ID: {$notification->id}");
                    return;
                }
                break;

            default:
                Log::warning("FCM: Unknown target type '{$notification->target_type}' for Notification ID: {$notification->id}");
                return;
        }

        // 2. Filter by student notification preferences
        if ($notification->categorie === 'Événement' || $notification->categorie === 'Planning') {
            $query->where('event_notifications', true);
        } elseif ($notification->categorie === 'Paiement') {
            $query->where('payment_notifications', true);
        }

        // 3. Fetch Tokens
        $tokens = $query->pluck('fcmToken')->toArray();
        $tokens = array_unique(array_filter($tokens));

        if (empty($tokens)) {
            Log::info("FCM: No active tokens found for targets. Broadcast aborted.");
            return;
        }

        Log::info("FCM: Targeting " . count($tokens) . " unique device(s).");
        foreach ($tokens as $index => $token) {
            Log::info("FCM Token #" . ($index + 1) . ": " . substr($token, 0, 15) . "...");
        }

        // 4. Construct and Send Message
        try {
            $message = CloudMessage::new()
                ->withNotification(FirebaseNotification::create(
                    $notification->titre,
                    $notification->message,
                    $notification->pieceJointe ? asset('storage/' . $notification->pieceJointe) : null
                ))
                ->withData([
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'id' => (string) $notification->id,
                    'type' => $notification->categorie ?? 'Info',
                ])
                ->withAndroidConfig([
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'classy_one_channel_v1',
                        'sound' => 'default',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'icon' => '@mipmap/ic_launcher',
                    ],
                ])
                ->withApnsConfig([
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'content-available' => 1,
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ]);

            // Firebase Multicast handles up to 500 tokens per request
            $report = $messaging->sendMulticast($message, $tokens);

            Log::info("FCM Result: Successes: " . $report->successes()->count() . " | Failures: " . $report->failures()->count());

            if ($report->hasFailures()) {
                foreach ($report->failures()->getItems() as $failure) {
                    Log::error("FCM Failure Details: " . $failure->error()->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error("FCM Critical Error: " . $e->getMessage());
        }
    }
}
