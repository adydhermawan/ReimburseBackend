<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Check existing users
$users = User::all();
echo "Current users: " . $users->count() . "\n";

foreach ($users as $user) {
    echo "- {$user->id}: {$user->email}\n";
}

// Create admin user
$admin = User::updateOrCreate(
    ['email' => 'admin@crocodic.com'],
    [
        'name' => 'Admin Crocodic',
        'password' => Hash::make('password123'),
    ]
);

echo "\nAdmin user created/updated:\n";
echo "ID: {$admin->id}\n";
echo "Email: {$admin->email}\n";
echo "Name: {$admin->name}\n";

echo "\nTotal users now: " . User::count() . "\n";
