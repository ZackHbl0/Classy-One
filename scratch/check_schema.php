<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = ['paiement', 'student', 'registre', 'audit_logs'];
foreach ($tables as $table) {
    echo "=== {$table} ===\n";
    if (Schema::hasTable($table)) {
        echo implode(', ', Schema::getColumnListing($table)) . "\n";
        echo "Count: " . Illuminate\Support\Facades\DB::table($table)->count() . "\n\n";
    } else {
        echo "DOES NOT EXIST\n\n";
    }
}
