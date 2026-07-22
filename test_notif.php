<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/notifications', 'POST');
$user = App\Models\Student::first();
$request->setUserResolver(function() use ($user) { return $user; });
$response = app()->handle($request);
echo $response->getContent();
