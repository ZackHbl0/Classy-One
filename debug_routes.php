<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Clearing caches...\n";
Artisan::call('route:clear');
Artisan::call('config:clear');
Artisan::call('view:clear');
Artisan::call('cache:clear');
echo Artisan::output();

echo "\nListing Filament routes:\n";
$routes = Route::getRoutes();
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'admin')) {
        echo implode('|', $route->methods()) . ' ' . $route->uri() . ' ' . $route->getName() . "\n";
    }
}
