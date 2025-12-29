<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\Artisan;

echo "<h1>Clearing Caches...</h1>";

try {
    echo "Clearing Route Cache... ";
    Artisan::call('route:clear');
    echo "OK<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

try {
    echo "Clearing Config Cache... ";
    Artisan::call('config:clear');
    echo "OK<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

try {
    echo "Clearing Cache... ";
    Artisan::call('cache:clear');
    echo "OK<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h3>Done!</h3>";
