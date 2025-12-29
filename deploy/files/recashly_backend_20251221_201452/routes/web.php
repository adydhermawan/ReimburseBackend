<?php

use Illuminate\Support\Facades\Route;

Route::get('/verify', function () {
    return file_get_contents(base_path('verify_log.txt'));
});

Route::get('/debug-log', function () {
    $path = storage_path('logs/laravel.log');
    if (!file_exists($path)) return 'Log empty';
    return '<pre>' . file_get_contents($path) . '</pre>';
});

Route::get('/', function () {
    return view('welcome');
});
