<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting reset admin script...\n";

try {
    require __DIR__ . '/vendor/autoload.php';
    echo "Autoload loaded.\n";

    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "Bootstrap loaded.\n";

    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "Kernel bootstrapped.\n";

    $email = 'admin@crocodic.com';
    $password = 'password123';
    
    echo "Updating user $email...\n";

    $user = \App\Models\User::firstOrNew(['email' => $email]);
    $user->name = 'Admin Crocodic';
    $user->password = \Illuminate\Support\Facades\Hash::make($password);
    $user->is_admin = true;
    $user->email_verified_at = now();
    $user->save();
    
    echo "SUCCESS: User updated.\n";
    echo "ID: " . $user->id . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Password: $password\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
