<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Notification;

$notifications = Notification::latest()->take(5)->get();
foreach ($notifications as $n) {
    echo "ID: {$n->id}\n";
    echo "Titre: {$n->titre}\n";
    echo "Type: {$n->target_type}\n";
    echo "Target IDs: " . json_encode($n->target_ids) . " (" . gettype($n->target_ids) . ")\n";
    echo "idStudent: {$n->idStudent}\n";
    echo "-------------------\n";
}
