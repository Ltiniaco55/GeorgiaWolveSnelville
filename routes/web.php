<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Home')->name('home');

if (app()->environment('local')) {
    Route::inertia('/styleguide', 'Styleguide')->name('styleguide');
}
