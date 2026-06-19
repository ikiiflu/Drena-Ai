<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard.index');
});

Route::get('/map', function () {
    return view('map.operational_map');
})->name('map.operational_map');

