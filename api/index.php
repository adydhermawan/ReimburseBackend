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
    // Quick debug endpoint - bypasses Laravel entirely to verify deployment is current
    if ($_SERVER['REQUEST_URI'] === '/api/vercel-test' || $_SERVER['REQUEST_URI'] === '/api/vercel-test/') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Vercel deployment is current (v4)',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'request_uri' => $_SERVER['REQUEST_URI'],
            'commit' => 'v4-route-debug'
        ]);
        exit;
    }
    
    // Deep debug - bootstrap Laravel and check routes
    if ($_SERVER['REQUEST_URI'] === '/api/debug-laravel' || $_SERVER['REQUEST_URI'] === '/api/debug-laravel/') {
        header('Content-Type: application/json');
        try {
            require __DIR__ . '/../vendor/autoload.php';
            
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
            
            // Delete route cache
            $routesCacheFile = $bootstrapCachePath . '/routes.php';
            if (file_exists($routesCacheFile)) {
                @unlink($routesCacheFile);
            }
            
            $app = require __DIR__ . '/../bootstrap/app.php';
            $app->useStoragePath($storagePath);
            $app->boot();
            
            $router = $app->make('router');
            $routes = collect($router->getRoutes())->map(fn($r) => [
                'uri' => $r->uri(),
                'methods' => $r->methods(),
            ])->take(50)->values()->toArray();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Laravel booted successfully',
                'route_count' => count($routes),
                'routes' => $routes,
            ]);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
        exit;
    }

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
        
        // IMPORTANT: Delete route cache to prevent stale routes on serverless
        // This ensures routes are always fresh loaded from routes/*.php files
        $routesCacheFile = $bootstrapCachePath . '/routes.php';
        if (file_exists($routesCacheFile)) {
            @unlink($routesCacheFile);
        }
        
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
