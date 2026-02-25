<?php
// Bootstrap Laravel to get a token and an ID
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$token = $user->createToken('test')->plainTextToken;

$reimbursement = \App\Models\Reimbursement::where('status', 'pending')->latest('id')->first();
if (!$reimbursement) {
    echo "No pending reimbursement found.\n";
    exit;
}

$id = $reimbursement->id;
echo "Testing ID: $id\n";
echo "Token: $token\n";

$url = "https://recashly.dadi.web.id/api/reimbursements/{$id}/process-ai";
$data = json_encode([
    'absolute_path' => '/tmp/doesnt_exist_so_it_will_download.jpg',
    'mime_type' => 'image/jpeg',
    'provider' => 'gemini'
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpcode\n";
echo "Error: $error\n";
echo "Result: $result\n";

