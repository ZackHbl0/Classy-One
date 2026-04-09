<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (!app()->runningInConsole()) {
            // Lock the root URL so all Laravel-generated URLs include subfolder + port
            URL::forceRootUrl(config('app.url'));

            // Set session cookie path to root so the cookie is sent
            // with ALL requests (including /livewire/update rewritten by Apache)
            config(['session.path' => '/']);
        }
    }
}
