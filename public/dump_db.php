<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h1>Database Schema Dumper</h1>";

$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$tableKey = "Tables_in_" . $dbName;

foreach ($tables as $table) {
    $tableName = $table->$tableKey;
    echo "<h2>Table: $tableName</h2>";
    echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
    $columns = DB::select("DESCRIBE `$tableName`");
    foreach ($columns as $column) {
        echo "<tr><td>{$column->Field}</td><td>{$column->Type}</td></tr>";
    }
    echo "</table>";
}
