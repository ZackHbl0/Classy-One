<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = App\Models\Student::first();
echo "Events: " . ($student->event_notifications ?? 'null') . "\n";
echo "Payments: " . ($student->payment_notifications ?? 'null') . "\n";
