<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('email', 'admin@osbt.com')->first();
echo "Name: " . $admin->name . "\n";
echo "Email: " . $admin->email . "\n";
echo "Matricule: " . $admin->matricule . "\n";
echo "Check 'password123': " . (Hash::check('password123', $admin->password) ? 'VALIDE' : 'NON') . "\n";
echo "Check '12345678': " . (Hash::check('12345678', $admin->password) ? 'VALIDE' : 'NON') . "\n";
