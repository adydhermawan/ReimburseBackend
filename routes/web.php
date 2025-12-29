<?php

use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});

Route::get('storage/{path}', function ($path) {
    return redirect(Illuminate\Support\Facades\Storage::url($path));
})->where('path', '.*');
