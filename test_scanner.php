<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $scanner = new \App\Services\AI\GeminiScanner();
    // Create a dummy image if needed, or use an existing one. For now just check instantiation.
    echo 'Scanner instantiated successfully.';
    if (empty(env('GEMINI_API_KEY'))) {
        echo ' WARNING: GEMINI_API_KEY is empty!';
    } else {
        echo ' API Key found.';
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

