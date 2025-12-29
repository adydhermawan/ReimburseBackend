<?php

use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Vercel Storage Configuration
|--------------------------------------------------------------------------
|
| Vercel is read-only, except for the /tmp directory. We need to tell
| Laravel to use /tmp for storage, views, logs, and cache.
|
*/
if (isset($_ENV['VERCEL_ENV']) || isset($_SERVER['VERCEL_ENV'])) {
    
    // Register the Composer autoloader...
    require __DIR__ . '/../vendor/autoload.php';

    // Function to recursively adjust paths
    $storagePath = '/tmp/storage';
    if (!is_dir($storagePath)) {
        mkdir($storagePath, 0777, true);
        mkdir($storagePath . '/app', 0777, true);
        mkdir($storagePath . '/framework/cache', 0777, true);
        mkdir($storagePath . '/framework/views', 0777, true);
        mkdir($storagePath . '/framework/sessions', 0777, true);
        mkdir($storagePath . '/logs', 0777, true);
    }
    
    // Override standard Laravel storage path
    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath($storagePath);
    
    // Run the application
    $app->handleRequest(Request::capture());
    exit;
}

// Forward Vercel requests to the Laravel index if not caught above (fallback)
require __DIR__ . '/../public/index.php';
