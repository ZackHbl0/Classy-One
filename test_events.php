<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = App\Models\Student::first();
$request = Illuminate\Http\Request::create('/api/events', 'POST', ['category' => 'Tout']);
$request->setUserResolver(function() use ($student) { return $student; });

$controller = new App\Http\Controllers\EventController();
$response = $controller->index($request);
echo $response->getContent();
