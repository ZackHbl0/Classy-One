<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SchoolParent;
use App\Models\Student;

$student = Student::first();
echo "First student: {$student->idStudent} - {$student->nom} {$student->prenom}\n";

$parent = SchoolParent::create([
    'name' => 'Test Parent',
    'email' => 'test.parent@osbt.com',
    'phone' => '0600000000',
    'password' => bcrypt('password123'),
]);

echo "Created parent ID: {$parent->id}\n";

$parent->students()->attach($student->idStudent);

$fetchedParent = SchoolParent::with('students')->find($parent->id);
echo "Fetched parent students count: " . $fetchedParent->students->count() . "\n";
foreach ($fetchedParent->students as $child) {
    echo "  -> Child: {$child->idStudent} - {$child->nom} {$child->prenom}\n";
}

$fetchedStudent = Student::with('parents')->find($student->idStudent);
echo "Fetched student parents count: " . $fetchedStudent->parents->count() . "\n";
foreach ($fetchedStudent->parents as $p) {
    echo "  -> Parent: {$p->id} - {$p->name}\n";
}

// Clean up test record
$parent->students()->detach();
$parent->delete();
echo "Cleaned up test record.\n";
