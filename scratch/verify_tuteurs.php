<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$students = App\Models\Student::take(10)->get(['matricule', 'nom', 'prenom', 'tuteur_name', 'numero_tuteur']);
echo "Total students count: " . App\Models\Student::count() . PHP_EOL;
echo "Students with tuteur_name populated: " . App\Models\Student::whereNotNull('tuteur_name')->count() . PHP_EOL . PHP_EOL;

foreach ($students as $s) {
    echo "{$s->matricule} | {$s->prenom} {$s->nom} | Tuteur: {$s->tuteur_name} (Tél: {$s->numero_tuteur})" . PHP_EOL;
}
