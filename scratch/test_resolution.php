<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$notif = App\Models\Notification::latest()->first();
if (!$notif) {
    echo "No notification in DB.\n";
    exit;
}

echo "Testing notification ID: {$notif->id} | Target: {$notif->target_type}\n";

try {
    // Run token resolution part of FcmService
    $query = App\Models\Student::query()->whereNotNull('fcmToken');
    switch ($notif->target_type) {
        case 'all':
            break;
        case 'students':
            $studentIds = $notif->target_ids ?? [];
            if ($notif->idStudent && !in_array($notif->idStudent, $studentIds)) {
                $studentIds[] = $notif->idStudent;
            }
            $query->whereIn('idStudent', $studentIds);
            break;
        case 'classes':
            $classIds = $notif->target_ids ?? [];
            if (!empty($classIds)) {
                $query->whereHas('registres', function ($q) use ($classIds) {
                    $q->whereIn('Cla_id', $classIds);
                });
            }
            break;
    }

    $studentTokens = $query->pluck('fcmToken')->toArray();
    echo "Student tokens count: " . count($studentTokens) . "\n";
    foreach ($studentTokens as $t) {
        echo " - " . substr($t, 0, 30) . "...\n";
    }

    $parentTokens = [];
    if ($notif->target_type === 'all') {
        $parentTokens = App\Models\SchoolParent::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
    } else {
        $targetStudents = (clone $query)->with('parents')->get();
        $parentTokens = $targetStudents->flatMap(function ($s) {
            return $s->parents->pluck('fcm_token');
        })->filter()->toArray();
    }
    echo "Parent tokens count: " . count($parentTokens) . "\n";

    $tokens = array_unique(array_filter(array_merge($studentTokens, $parentTokens)));
    echo "Total unique tokens targeted: " . count($tokens) . "\n";
} catch (\Throwable $e) {
    echo "ERROR during resolution: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
