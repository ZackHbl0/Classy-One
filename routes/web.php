<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/panel');
});


// Manual routes removed to allow Filament's internal routing to work correctly
