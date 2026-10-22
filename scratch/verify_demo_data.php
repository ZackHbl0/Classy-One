<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Classe;
use App\Models\Student;
use App\Models\Planning;
use App\Models\Course;

echo "==================== VERIFICATION REPORT ====================\n";

// 1. Staff
$sec = User::where('role', 'secretaire')->get();
echo "1. SECRETAIRE(S): " . $sec->count() . "\n";
foreach ($sec as $s) {
    echo "   - {$s->matricule}: {$s->name} ({$s->email})\n";
}

$profs = User::where('role', 'professeur')->get();
echo "\n2. PROFESSORS: " . $profs->count() . "\n";
foreach ($profs as $p) {
    $mat = is_array($p->matieres) ? implode(', ', $p->matieres) : $p->matieres;
    $classesCount = $p->classes()->count();
    echo "   - {$p->matricule}: {$p->name} | Matieres: {$mat} | Classes: {$classesCount}\n";
}

// 2. Classes & Students
$classes = Classe::all();
echo "\n3. CLASSES & STUDENTS DISTRIBUTION:\n";
foreach ($classes as $c) {
    $count = $c->registres()->count();
    echo "   - Classe #{$c->id} [{$c->nomClasse}]: {$count} registered students\n";
}

$totalStudents = Student::count();
echo "   Total Students: {$totalStudents}\n";

// 3. Timetables & Overlap check
$plannings = Planning::all();
echo "\n4. TIMETABLE ENTRIES: " . $plannings->count() . "\n";

$overlaps = 0;
// Check professor overlaps
$groupedByProfSlot = $plannings->groupBy(function($item) {
    return $item->jour . '|' . $item->check_in . '|' . $item->professeur_name;
});
foreach ($groupedByProfSlot as $key => $group) {
    if ($group->count() > 1) {
        echo "   [WARNING] Professor overlap on {$key}: " . $group->count() . " occurrences\n";
        $overlaps++;
    }
}

// Check class overlaps
$groupedByClassSlot = $plannings->groupBy(function($item) {
    return $item->jour . '|' . $item->check_in . '|' . $item->classe_id;
});
foreach ($groupedByClassSlot as $key => $group) {
    if ($group->count() > 1) {
        echo "   [WARNING] Class overlap on {$key}: " . $group->count() . " occurrences\n";
        $overlaps++;
    }
}

// Check room overlaps
$groupedByRoomSlot = $plannings->groupBy(function($item) {
    return $item->jour . '|' . $item->check_in . '|' . $item->salle;
});
foreach ($groupedByRoomSlot as $key => $group) {
    if ($group->count() > 1) {
        echo "   [WARNING] Room overlap on {$key}: " . $group->count() . " occurrences\n";
        $overlaps++;
    }
}

if ($overlaps === 0) {
    echo "   ✅ ZERO overlaps detected! All professor, class, and room schedules are perfectly conflict-free.\n";
}

// 4. Courses
$courses = Course::count();
echo "\n5. COURSE MATERIALS: {$courses} items created.\n";

echo "=============================================================\n";
