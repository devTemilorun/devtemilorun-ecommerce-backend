<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;


Route::get('/', function () {
    return view('welcome');
});



Route::get('/run-seeder-secret-xyz', function () {
    Artisan::call('db:seed', ['--force' => true]);
    return 'Seeded!';
});