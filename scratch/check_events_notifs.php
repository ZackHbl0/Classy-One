<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Event;
use App\Models\Notification;

echo "Events count: " . Event::count() . "\n";
foreach (Event::all() as $e) {
    echo "- [{$e->categorie}] {$e->titre} ({$e->date_evenement}) @ {$e->lieu} - Prix: {$e->prix} MAD\n";
}

echo "\nNotifications count: " . Notification::count() . "\n";
foreach (Notification::take(5)->get() as $n) {
    echo "- [{$n->cible_type}] {$n->titre} : {$n->message}\n";
}
