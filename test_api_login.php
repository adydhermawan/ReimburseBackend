<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

echo "Testing Internal API Login (HTTP Kernel v2)...\n";

// Ensure user exists
$email = 'mobile@crocodic.com';
$password = 'password123';
$user = \App\Models\User::firstOrCreate(
    ['email' => $email], 
    ['name' => 'Mobile Generic', 'password' => \Illuminate\Support\Facades\Hash::make($password), 'is_admin' => false]
);
$user->password = \Illuminate\Support\Facades\Hash::make($password);
$user->save();

// Simulate Request
$request = Request::create('/api/auth/login', 'POST', [
    'email' => $email,
    'password' => $password
]);

// Handle
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
