<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$apiKey = config('services.gemini.key');
$url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";

echo "Fetching models from: $url\n\n";

$response = Http::get($url);

if ($response->failed()) {
    echo "Error: " . $response->body() . "\n";
    exit(1);
}

$models = $response->json()['models'] ?? [];
foreach ($models as $model) {
    if (in_array('generateContent', $model['supportedGenerationMethods'])) {
        echo " - " . $model['name'] . "\n";
    }
}
