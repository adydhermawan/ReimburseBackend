<?php
// debug_login_v3.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// USE HTTP KERNEL EXPLICITLY
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "<h1>Debug Login V3 (HTTP Kernel)</h1>";

try {
    // 1. Test Tables
    echo "<h3>1. Database Check</h3>";
    $count = \DB::table('users')->count();
    echo "Users Table Count: $count <br>";
    
    // 2. Create/Update User
    echo "<h3>2. User Setup</h3>";
    $email = 'mobile@crocodic.com';
    $password = 'password123';
    
    $user = \App\Models\User::firstOrCreate(
        ['email' => $email], 
        ['name' => 'Mobile Generic', 'password' => \Illuminate\Support\Facades\Hash::make($password), 'is_admin' => false]
    );
    // Reset password to be sure
    $user->password = \Illuminate\Support\Facades\Hash::make($password);
    $user->save();
    
    echo "User found/created: " . $user->email . " (ID: " . $user->id . ")<br>";
    
    // 3. Manual Login Attempt
    echo "<h3>3. Auth Attempt</h3>";
    if (\Illuminate\Support\Facades\Auth::attempt(['email' => $email, 'password' => $password])) {
        echo "<span style='color:green'>LOGIN SUCCESSFUL!</span><br>";
        $user = \Illuminate\Support\Facades\Auth::user();
        echo "Logged in as: " . $user->name . "<br>";
        
        // Generate Token
        $token = $user->createToken('debug-token')->plainTextToken;
        echo "<strong>Token Generated:</strong> " . substr($token, 0, 10) . "...<br>";
        
    } else {
        echo "<span style='color:red'>LOGIN FAILED</span><br>";
        echo "Check password hashing or DB driver.<br>";
    }

} catch (Exception $e) {
    echo "<h2 style='color:red'>EXCEPTION</h2>";
    echo $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
