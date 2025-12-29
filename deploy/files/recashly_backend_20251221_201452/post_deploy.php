<?php
// post_deploy.php - Jalankan sekali via browser
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h1>Recashly Post-Deploy</h1>";
echo "<pre>";

try {
    // Cache config
    echo "Running config:cache...\n";
    $kernel->call('config:cache');
    echo "Running route:cache...\n";
    $kernel->call('route:cache');
    echo "Running view:cache...\n";
    $kernel->call('view:cache');

    // Create storage link
    echo "Checking storage link...\n";
    if (!file_exists(public_path('storage'))) {
        echo "Creating symlink...\n";
        symlink(storage_path('app/public'), public_path('storage'));
        echo "Link created.\n";
    } else {
        echo "Link already exists.\n";
    }

    echo "\n✅ Post-deployment completed successfully!";
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage();
}

echo "</pre>";
