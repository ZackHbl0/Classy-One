<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$students = App\Models\Student::whereNotNull('fcmToken')->get(['idStudent', 'matricule', 'nom', 'prenom', 'fcmToken']);
echo "Found " . $students->count() . " student(s) with FCM token:\n";
foreach ($students as $s) {
    echo "Student ID {$s->idStudent} ({$s->matricule} - {$s->nom} {$s->prenom}): " . substr($s->fcmToken, 0, 40) . "...\n";
}

$parents = App\Models\SchoolParent::whereNotNull('fcm_token')->get(['id', 'nom', 'prenom', 'fcm_token']);
echo "Found " . $parents->count() . " parent(s) with FCM token:\n";
foreach ($parents as $p) {
    echo "Parent ID {$p->id} ({$p->nom} {$p->prenom}): " . substr($p->fcm_token, 0, 40) . "...\n";
}
