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
    
    // --- TEMPORARY: CREATE ADMIN USER ---
    // Access via /api/create-admin to create the first admin user
    // DELETE THIS BLOCK AFTER USE!
    if (strpos($_SERVER['REQUEST_URI'], 'create-admin') !== false) {
        try {
            require __DIR__ . '/../vendor/autoload.php';
            
            // Setup storage paths (same as main app)
            $storagePath = '/tmp/storage';
            $bootstrapCachePath = '/tmp/bootstrap/cache';
            
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0777, true);
                mkdir($storagePath . '/app', 0777, true);
                mkdir($storagePath . '/framework/cache', 0777, true);
                mkdir($storagePath . '/framework/views', 0777, true);
                mkdir($storagePath . '/framework/sessions', 0777, true);
                mkdir($storagePath . '/logs', 0777, true);
            }
            if (!is_dir($bootstrapCachePath)) {
                mkdir($bootstrapCachePath, 0777, true);
            }
            
            $_ENV['APP_SERVICES_CACHE'] = $bootstrapCachePath . '/services.php';
            $_ENV['APP_PACKAGES_CACHE'] = $bootstrapCachePath . '/packages.php';
            $_ENV['APP_CONFIG_CACHE'] = $bootstrapCachePath . '/config.php';
            $_ENV['APP_ROUTES_CACHE'] = $bootstrapCachePath . '/routes.php';
            $_ENV['APP_EVENTS_CACHE'] = $bootstrapCachePath . '/events.php';
            
            $app = require __DIR__ . '/../bootstrap/app.php';
            $app->useStoragePath($storagePath);
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            
            $email = 'admin@recashly.com';
            $password = 'recashly2024';
            
            $user = \App\Models\User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Admin',
                    'password' => bcrypt($password),
                    'is_admin' => true,
                ]
            );
            
            header('Content-Type: text/plain');
            echo "Admin Created/Updated!\n";
            echo "Email: $email\n";
            echo "Password: $password\n";
            echo "\n*** DELETE THIS ROUTE AFTER LOGIN! ***\n";
        } catch (\Throwable $e) {
            header('Content-Type: text/plain');
            echo "Error: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
        exit;
    }
    // --- END TEMPORARY ---
    
    try {
        // Register the Composer autoloader...
        require __DIR__ . '/../vendor/autoload.php';

        // Function to recursively adjust paths
        $storagePath = '/tmp/storage';
        $bootstrapCachePath = '/tmp/bootstrap/cache';

        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
            mkdir($storagePath . '/app', 0777, true);
            mkdir($storagePath . '/framework/cache', 0777, true);
            mkdir($storagePath . '/framework/views', 0777, true);
            mkdir($storagePath . '/framework/sessions', 0777, true);
            mkdir($storagePath . '/logs', 0777, true);
        }

        if (!is_dir($bootstrapCachePath)) {
            mkdir($bootstrapCachePath, 0777, true);
        }
        
        // Redirect Laravel caches to /tmp (since project dir is read-only)
        $_ENV['APP_SERVICES_CACHE'] = $bootstrapCachePath . '/services.php';
        $_ENV['APP_PACKAGES_CACHE'] = $bootstrapCachePath . '/packages.php';
        $_ENV['APP_CONFIG_CACHE'] = $bootstrapCachePath . '/config.php';
        $_ENV['APP_ROUTES_CACHE'] = $bootstrapCachePath . '/routes.php';
        $_ENV['APP_EVENTS_CACHE'] = $bootstrapCachePath . '/events.php';
        
        // Override standard Laravel storage path
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->useStoragePath($storagePath);
        
        // Run the application
        $app->handleRequest(Request::capture());
    } catch (\Throwable $e) {
        http_response_code(500);
        echo "🔥 Vercel Error: " . $e->getMessage() . "<br>";
        echo "File: " . $e->getFile() . " on line " . $e->getLine() . "<br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        
        // Also log to stderr for Vercel logs
        error_log($e->getMessage());
        error_log($e->getTraceAsString());
    }
    exit;
}

// Forward Vercel requests to the Laravel index if not caught above (fallback)
require __DIR__ . '/../public/index.php';
