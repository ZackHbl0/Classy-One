<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Manual routes removed to allow Filament's internal routing to work correctly
