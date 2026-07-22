<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $event = App\Models\Event::create([
        'titre' => 'Test Filament',
        'description' => 'Test sans prix',
        'date_evenement' => '2026-08-01 10:00:00',
        'lieu' => 'En ligne',
        'categorie' => 'Événement',
        // 'prix' => null // simulate filament
    ]);
    echo "Event created ID: " . $event->id . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
