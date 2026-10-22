<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SchoolParent;
use Illuminate\Support\Facades\Hash;

$parent = SchoolParent::where('email', 'mohamed.alami@parent.osbt.com')->first();
echo "Found parent: {$parent->name}\n";
echo "Password verification for 'password123': " . (Hash::check('password123', $parent->password) ? 'SUCCESS' : 'FAILED') . "\n";
echo "Password verification for phone login with '0770000001': " . (SchoolParent::where('phone', '0770000001')->exists() ? 'FOUND BY PHONE' : 'NOT FOUND') . "\n";
