<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Reimbursement;
use App\Models\Client;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Starting Real Data Seeder ===\n";

try {
    // 1. Truncate tables
    echo "Truncating reimbursements and clients tables...\n";
    Schema::disableForeignKeyConstraints();
    Reimbursement::truncate();
    Client::truncate();
    Schema::enableForeignKeyConstraints();
    echo "Tables truncated successfully.\n";

    // Get Admin User
    $user = User::where('email', 'admin@crocodic.com')->first();
    if (!$user) {
        $user = User::create([
            'name' => 'Admin Crocodic',
            'email' => 'admin@crocodic.com',
            'password' => bcrypt('password123'),
        ]);
        echo "Created admin user.\n";
    } else {
        echo "Found existing admin user: {$user->name}\n";
    }

    // Get Categories
    $categories = Category::all()->pluck('id', 'name');
    echo "Found " . count($categories) . " categories.\n";

    if (count($categories) == 0) {
        echo "ERROR: No categories found. Run CategorySeeder first.\n";
        exit(1);
    }

    // Helper to find category
    $getCategoryId = function($desc) use ($categories) {
        $desc = strtolower($desc);
        if (str_contains($desc, 'bensin')) return $categories['Transportasi'] ?? 1;
        if (str_contains($desc, 'taxi') || str_contains($desc, 'ojol')) return $categories['Transportasi'] ?? 1;
        if (str_contains($desc, 'makan')) return $categories['Makan'] ?? 2;
        if (str_contains($desc, 'kopi')) return $categories['Makan'] ?? 2;
        if (str_contains($desc, 'parkir')) return $categories['Parkir'] ?? 3;
        if (str_contains($desc, 'toll') || str_contains($desc, 'tol') || str_contains($desc, 'etoll')) return $categories['Tol'] ?? 4;
        return $categories['Lainnya'] ?? 8;
    };

    // Data
    $data = [
        ['04/11/2025', 'Bensin', 'ACE Medical', 100000],
        ['04/11/2025', 'Makan', 'ACE Medical', 67000],
        ['04/11/2025', 'Kopi', 'ACE Medical', 29000],
        ['9 Nov 2025', 'Bensin', 'Perjalalan Stasiun', 100000],
        ['10 Nov 2025', 'Taxi / Ojol', 'Agung Sedayu', 45000],
        ['10 Nov 2025', 'Taxi / Ojol', 'Agung Sedayu', 111400],
        ['10 Nov 2025', 'Taxi / Ojol', 'Agung Sedayu', 115500],
        ['10/11/2025', 'Makan', 'Agung Sedayu', 79000],
        ['10 Nov 2025', 'Taxi / Ojol', 'Agung Sedayu', 62000],
        ['10 Nov 2025', 'Parkir & Toll', 'Agung Sedayu', 22000],
        ['10 Nov 2025', 'Kopi', 'Agung Sedayu', 61000],
        ['10 Nov 2025', 'Makan', 'Agung Sedayu', 35000],
        ['10 Nov 2025', 'Taxi / Ojol', 'Agung Sedayu', 26000],
        ['11 Nov 2025', 'Taxi / Ojol', 'Hometown', 31400],
        ['11/11/2025', 'Makan', 'Hometown', 45000],
        ['11 Nov 2025', 'Parkir', 'Hometown', 5000],
        ['11 Nov 2025', 'Taxi / Ojol', 'Hometown', 60000],
        ['11 Nov 2025', 'Parkir', 'Hometown', 5000],
        ['11 Nov 2025', 'Taxi / Ojol', 'Hometown', 34000],
        ['11 Nov 2025', 'Makan', 'Hometown', 110000],
        ['11 Nov 2025', 'Parkir stasiun', 'Perjalalan Stasiun', 100000],
        ['11 Nov 2025', 'Bensin', 'Perjalalan Stasiun', 100000],
        ['20/11/2025', 'Taxi / Ojol', 'Hometown', 217000],
        ['20 Nov 2025', 'Makan', 'Hometown', 54000],
        ['20 Nov 2025', 'Kopi', 'Hometown', 26500],
        ['20 Nov 2025', 'Taxi / Ojol', 'Hometown', 33700],
        ['20/11/2025', 'Makan', 'Hometown', 54000],
        ['20 Nov 2025', 'Taxi / Ojol', 'Hometown', 34500],
        ['20 Nov 2025', 'Toll & Parkir', 'Hometown', 61000],
        ['20/12/2025', 'Kopi', 'Hometown', 26500],
        ['20 Nov 2025', 'Taxi / Ojol', 'Hometown', 34500],
        ['20 Nov 2025', 'Makan', 'Hometown', 80000],
        ['21 Nov 2025', 'Kopi', 'Hometown', 48000],
        ['21 Nov 2025', 'Taxi / Ojol', 'Hometown', 59500],
        ['21 Nov 2025', 'Kopi', 'Hometown', 95800],
        ['21 Nov 2025', 'Taxi / Ojol', 'Hometown', 120000],
        ['21 Nov 2025', 'Toll & Parkir', 'Hometown', 18500],
        ['21 Nov 2025', 'Makan', 'Hometown', 131000],
        ['22 Nov 2025', 'Parkir Stasiun', 'Perjalalan Stasiun', 100000],
        ['22 Nov 2025', 'Bensin', 'Perjalalan Stasiun', 200000],
        ['22 Nov 2025', 'Makan', 'Perjalalan Stasiun', 50800],
        ['26 Nov 2025', 'Bensin', 'Industropolis Batang', 103000],
        ['26 Nov 2025', 'Top-Up Etoll', 'Wadimor', 100000],
        ['26 Nov 2025', 'Kopi', 'Wadimor', 47700],
    ];

    echo "Inserting " . count($data) . " reimbursements...\n";
    
    $inserted = 0;
    foreach ($data as $index => $row) {
        $dateStr = $row[0];
        $desc = $row[1];
        $clientName = trim($row[2]);
        $amount = $row[3];

        // Parse Date
        try {
            if (str_contains($dateStr, '/')) {
                $date = Carbon::createFromFormat('d/m/Y', $dateStr);
            } else {
                $date = Carbon::parse($dateStr);
            }
        } catch (\Exception $e) {
            $date = Carbon::now();
            echo "Warning: Could not parse date '{$dateStr}', using current date.\n";
        }

        // Find or Create Client
        $client = Client::firstOrCreate(['name' => $clientName]);

        // Create Reimbursement
        $reimbursement = Reimbursement::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'category_id' => $getCategoryId($desc),
            'amount' => $amount,
            'transaction_date' => $date->format('Y-m-d'),
            'note' => $desc,
            'image_path' => 'placeholders/receipt_placeholder.jpg',
            'status' => 'pending',
        ]);

        $inserted++;
        if ($inserted % 10 == 0) {
            echo "Inserted {$inserted} records...\n";
        }
    }

    echo "\n=== SEEDING COMPLETE ===\n";
    echo "Total Reimbursements: " . Reimbursement::count() . "\n";
    echo "Total Clients: " . Client::count() . "\n";
    echo "\nClients Created:\n";
    foreach (Client::all() as $c) {
        echo "  - {$c->name}\n";
    }

    echo "\nFirst 3 Reimbursements:\n";
    foreach (Reimbursement::with(['client', 'category'])->take(3)->get() as $r) {
        echo "  - {$r->transaction_date} | {$r->note} | {$r->client->name} | Rp" . number_format($r->amount, 0, ',', '.') . "\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
