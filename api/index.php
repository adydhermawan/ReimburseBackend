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
    
    // --- DEBUGGING DATABASE CONNECTION ---
    // Access this via /api/test-db-connection to debug
    if (strpos($_SERVER['REQUEST_URI'], 'test-db-connection') !== false) {
        header('Content-Type: text/plain');
        echo "=== TiDB Connection Debugger ===\n\n";
        
        $host = $_ENV['DB_HOST'] ?? 'NOT SET';
        $port = $_ENV['DB_PORT'] ?? '4000';
        $db   = $_ENV['DB_DATABASE'] ?? 'test';
        $user = $_ENV['DB_USERNAME'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';
        
        echo "Config:\nHost: $host\nPort: $port\nUser: $user\nDatabase: $db\n\n";
        
        // Scenario 1: Standard (No SSL Options)
        echo "1. Attempting connection WITHOUT SSL options...\n";
        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$db";
            $pdo = new PDO($dsn, $user, $pass);
            echo "SUCCESS!\n";
        } catch (PDOException $e) {
            echo "FAILED: " . $e->getMessage() . "\n";
        }
        echo "\n";

        // Scenario 2: SSL Verify = False
        echo "2. Attempting connection with SSL_VERIFY_SERVER_CERT = false...\n";
        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$db;ssl-mode=VERIFY_IDENTITY"; // Try forcing ssl-mode in DSN
            $options = [
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];
            $pdo = new PDO($dsn, $user, $pass, $options);
            echo "SUCCESS!\n";
        } catch (PDOException $e) {
            echo "FAILED: " . $e->getMessage() . "\n";
        }
        echo "\n";
        
        // Scenario 3: System CA
        echo "3. Attempting connection with System CA...\n";
        $ca = collect([
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/ssl/ca-bundle.pem',
            '/usr/local/share/ca-certificates/cacert.pem',
        ])->first(fn($path) => file_exists($path));
        
        echo "Detected CA Path: " . ($ca ?? 'NONE') . "\n";
        
        if ($ca) {
            try {
                $options = [
                    PDO::MYSQL_ATTR_SSL_CA => $ca,
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
                ];
                $pdo = new PDO($dsn, $user, $pass, $options);
                echo "SUCCESS!\n";
            } catch (PDOException $e) {
                echo "FAILED: " . $e->getMessage() . "\n";
            }
        } else {
             echo "SKIPPED (No CA found)\n";
        }
        
        exit;
    }
    // --- END DEBUGGING ---
    
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
