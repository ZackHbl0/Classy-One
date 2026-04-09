<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Capture current request for debugging
$request = Request::capture();

echo "<h1>Diagnostic Tool v2</h1>";

echo "<h2>Incoming Request Info</h2>";
echo "<ul>";
echo "<li><b>Full URL:</b> " . $request->fullUrl() . "</li>";
echo "<li><b>Path Info:</b> '" . $request->getPathInfo() . "'</li>";
echo "<li><b>Request URI:</b> '" . $request->getRequestUri() . "'</li>";
echo "<li><b>Base URL:</b> '" . $request->getBaseUrl() . "'</li>";
echo "<li><b>Method:</b> " . $request->method() . "</li>";
echo "<li><b>Session Cookie:</b> " . config('session.cookie') . "</li>";
echo "<li><b>Session Path:</b> " . config('session.path') . "</li>";
echo "</ul>";

echo "<h2>Clearing Caches...</h2>";
Artisan::call('route:clear');
Artisan::call('config:clear');
Artisan::call('cache:clear');
Artisan::call('view:clear');
echo "<pre>" . Artisan::output() . "</pre>";

echo "<h2>Full Route List</h2>";
echo "<table border='1'><tr><th>Method</th><th>URI</th><th>Name</th><th>Action</th></tr>";
$routes = Route::getRoutes();
foreach ($routes as $route) {
    echo "<tr>";
    echo "<td>" . implode(', ', $route->methods()) . "</td>";
    echo "<td>" . $route->uri() . "</td>";
    echo "<td>" . $route->getName() . "</td>";
    echo "<td>" . $route->getActionName() . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p>Try testing the login now at <a href='/osbt-api/public/panel/login'>/panel/login</a></p>";
