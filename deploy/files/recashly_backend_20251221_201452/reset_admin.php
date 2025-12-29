<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = \App\Models\User::firstOrNew(['email' => 'admin@crocodic.com']);
    $user->name = 'Admin Crocodic';
    $user->password = \Illuminate\Support\Facades\Hash::make('password123');
    $user->is_admin = true;
    $user->email_verified_at = now();
    $user->save();
    
    echo "SUCCESS: User updated via Script. ID: " . $user->id . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Is Admin: " . ($user->is_admin ? 'Yes' : 'No') . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
