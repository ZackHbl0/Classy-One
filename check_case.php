<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Student;

$s1 = Student::where('matricule', 'DI2005')->first();
$s2 = Student::where('matricule', 'di2005')->first();

echo "DI2005 ID: " . ($s1 ? $s1->idStudent : "None") . "\n";
echo "di2005 ID: " . ($s2 ? $s2->idStudent : "None") . "\n";
