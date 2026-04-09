<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Student;
use App\Models\Registre;

$s = Student::where('matricule', 'DI2005')->first();
if ($s) {
    echo "Student ID: " . $s->idStudent . "\n";
    $r = Registre::where('idStudent', $s->idStudent)->first();
    echo "Class ID: " . ($r ? $r->Cla_id : "Not Found") . "\n";
} else {
    echo "Student DI2005 not found.\n";
}
