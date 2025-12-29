<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Creating Filament Asset Symlinks ===\n\n";

// Create directories
$cssDirs = ['css/filament/filament', 'css/filament/forms', 'css/filament/support', 'css/filament/tables', 'css/filament/notifications', 'css/filament/infolists', 'css/filament/widgets', 'css/filament/actions'];
$jsDirs = ['js/filament/filament', 'js/filament/forms', 'js/filament/support', 'js/filament/tables', 'js/filament/notifications', 'js/filament/infolists', 'js/filament/widgets', 'js/filament/actions'];

foreach ($cssDirs as $dir) {
    $fullPath = public_path($dir);
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "Created: $fullPath\n";
    }
}

foreach ($jsDirs as $dir) {
    $fullPath = public_path($dir);
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "Created: $fullPath\n";
    }
}

// Map Filament packages to their dist directories
$packages = [
    'filament' => 'vendor/filament/filament/dist',
    'forms' => 'vendor/filament/forms/dist',
    'support' => 'vendor/filament/support/dist',
    'tables' => 'vendor/filament/tables/dist',
    'notifications' => 'vendor/filament/notifications/dist',
    'infolists' => 'vendor/filament/infolists/dist',
    'widgets' => 'vendor/filament/widgets/dist',
    'actions' => 'vendor/filament/actions/dist',
];

foreach ($packages as $name => $distPath) {
    $fullDistPath = base_path($distPath);
    
    if (!is_dir($fullDistPath)) {
        echo "Skip: $name (dist not found at $fullDistPath)\n";
        continue;
    }
    
    // Copy CSS files
    $cssFiles = glob($fullDistPath . '/*.css');
    foreach ($cssFiles as $cssFile) {
        $filename = basename($cssFile);
        $destFile = public_path("css/filament/{$name}/{$filename}");
        copy($cssFile, $destFile);
        echo "Copied CSS: $cssFile -> $destFile\n";
    }
    
    // Also create app.css symlink pointing to index.css or theme.css
    $indexCss = $fullDistPath . '/index.css';
    $themeCss = $fullDistPath . '/theme.css';
    $appCss = public_path("css/filament/{$name}/app.css");
    
    if (file_exists($themeCss)) {
        copy($themeCss, $appCss);
        echo "Created app.css from theme.css for $name\n";
    } elseif (file_exists($indexCss)) {
        copy($indexCss, $appCss);
        echo "Created app.css from index.css for $name\n";
    }
    
    // Copy JS files
    $jsFiles = glob($fullDistPath . '/*.js');
    foreach ($jsFiles as $jsFile) {
        $filename = basename($jsFile);
        $destFile = public_path("js/filament/{$name}/{$filename}");
        copy($jsFile, $destFile);
        echo "Copied JS: $jsFile -> $destFile\n";
    }
    
    // Also create app.js symlink pointing to index.js
    $indexJs = $fullDistPath . '/index.js';
    $appJs = public_path("js/filament/{$name}/app.js");
    
    if (file_exists($indexJs)) {
        copy($indexJs, $appJs);
        echo "Created app.js from index.js for $name\n";
    }
}

echo "\n=== Done ===\n";
echo "Public directory contents:\n";
print_r(scandir(public_path('css/filament')));
