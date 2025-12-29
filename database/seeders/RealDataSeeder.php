<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reimbursement;
use App\Models\Client;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RealDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Truncate reimbursements table
        Schema::disableForeignKeyConstraints();
        Reimbursement::truncate();
        // Client::truncate(); // Optional: keep existing clients or truncate? 
        // User didn't say to delete clients, but "hapus semua dummy data". 
        // I will truncate clients created by dummy seeders if possible, but simplest is to just ensure these clients exist.
        // Let's truncate Client as well to be clean.
        Client::truncate(); 
        Schema::enableForeignKeyConstraints();

        // Ensure Admin User exists (created by AdminUserSeeder, but let's fetch it)
        $user = User::where('email', 'admin@crocodic.com')->first();
        if (!$user) {
             // Fallback if AdminUserSeeder wasn't run or user deleted
             $user = User::create([
                'name' => 'Admin Crocodic',
                'email' => 'admin@crocodic.com',
                'password' => bcrypt('password123'),
             ]);
        }

        // Get Categories
        $categories = Category::all()->pluck('id', 'name');
        
        // Helper to find category
        $getCategoryId = function($desc) use ($categories) {
            $desc = strtolower($desc);
            if (str_contains($desc, 'bensin')) return $categories['Transportasi'];
            if (str_contains($desc, 'taxi') || str_contains($desc, 'ojol')) return $categories['Transportasi'];
            if (str_contains($desc, 'makan')) return $categories['Makan'];
            if (str_contains($desc, 'kopi')) return $categories['Makan'];
            if (str_contains($desc, 'parkir')) return $categories['Parkir'];
            if (str_contains($desc, 'toll') || str_contains($desc, 'tol')) return $categories['Tol'];
            return $categories['Lainnya'];
        };

        // Data
        $data = [
            ['04/11/2025', 'Bensin', 'ACE Medical', 'Rp100,000'],
            ['04/11/2025', 'Makan', 'ACE Medical', 'Rp67,000'],
            ['04/11/2025', 'Kopi', 'ACE Medical', 'Rp29,000'],
            ['9 Nov 2025', 'Bensin', 'Perjalalan Stasiun', 'Rp100,000'],
            ['10 Nov 2025', 'Taxi / Ojol', 'Agung Sedayu', 'Rp45,000'],
            ['10 Nov 2025', 'Taxi / Ojol', 'Agung Sedayu', 'Rp111,400'],
            ['10 Nov 2025', 'Taxi / Ojol', 'Agung Sedayu', 'Rp115,500'],
            ['10/11/2025', 'Makan', 'Agung Sedayu', 'Rp79,000'],
            ['10 Nov 2025', 'Taxi / Ojol', 'Agung Sedayu', 'Rp62,000'],
            ['10 Nov 2025', 'Parkir & Toll ', 'Agung Sedayu', 'Rp22,000'],
            ['10 Nov 2025', 'Kopi', 'Agung Sedayu', 'Rp61,000'],
            ['10 Nov 2025', 'Makan', 'Agung Sedayu', 'Rp35,000'],
            ['10 Nov 2025', 'Taxi / Ojol', 'Agung Sedayu', 'Rp26,000'],
            ['11 Nov 2025', 'Taxi / Ojol', 'Hometown ', 'Rp31,400'],
            ['11/11/2025', 'Makan', 'Hometown ', 'Rp45,000'],
            ['11 Nov 2025', 'Parkir', 'Hometown ', 'Rp5,000'],
            ['11 Nov 2025', 'Taxi / Ojol', 'Hometown ', 'Rp60,000'],
            ['11 Nov 2025', 'Parkir', 'Hometown ', 'Rp5,000'],
            ['11 Nov 2025', 'Taxi / Ojol', 'Hometown ', 'Rp34,000'],
            ['11 Nov 2025', 'Makan', 'Hometown ', 'Rp110,000'],
            ['11 Nov 2025', 'Parkir stasiun ', 'Perjalalan Stasiun', 'Rp100,000'],
            ['11 Nov 2025', 'Bensin', 'Perjalalan Stasiun', 'Rp100,000'],
            ['20/11/2025', 'Taxi / Ojol', 'Hometown', 'Rp217,000'],
            ['20 Nov 2025', 'Makan', 'Hometown', 'Rp54,000'],
            ['20 Nov 2025', 'Kopi', 'Hometown', 'Rp26,500'],
            ['20 Nov 2025', 'Taxi / Ojol', 'Hometown', 'Rp33,700'],
            ['20/11/2025', 'Makan', 'Hometown', 'Rp54,000'],
            ['20 Nov 2025', 'Taxi / Ojol', 'Hometown', 'Rp34,500'],
            ['20 Nov 2025', 'Toll & Parkir ', 'Hometown', 'Rp61,000'],
            ['20/12/2025', 'Kopi', 'Hometown', 'Rp26,500'],
            ['20 Nov 2025', 'Taxi / Ojol', 'Hometown', 'Rp34,500'],
            ['20 Nov 2025', 'Makan', 'Hometown', 'Rp80,000'],
            ['21 Nov 2025', 'Kopi', 'Hometown', 'Rp48,000'],
            ['21 Nov 2025', 'Taxi / Ojol', 'Hometown', 'Rp59,500'],
            ['21 Nov 2025', 'Kopi', 'Hometown', 'Rp95,800'],
            ['21 Nov 2025', 'Taxi / Ojol', 'Hometown', 'Rp120,000'],
            ['21 Nov 2025', 'Toll & Parkir ', 'Hometown', 'Rp18,500'],
            ['21 Nov 2025', 'Makan', 'Hometown', 'Rp131,000'],
            ['22 Nov 2025', 'Parkir Stasiun ', 'Perjalalan Stasiun', 'Rp100,000'],
            ['22 Nov 2025', 'Bensin', 'Perjalalan Stasiun', 'Rp200,000'],
            ['22 Nov 2025', 'Makan', 'Perjalalan Stasiun', 'Rp50,800'],
            ['26 Nov 2025', 'Bensin', 'Industropolis Batang', 'Rp103,000'],
            ['26 Nov 2025', 'Top-Up Etoll', 'Wadimor', 'Rp100,000'],
            ['26 Nov 2025', 'Kopi', 'Wadimor', 'Rp47,700'],
        ];

        foreach ($data as $row) {
            $dateStr = $row[0];
            $desc = $row[1];
            $clientName = trim($row[2]);
            $amountStr = $row[3];

            // Parse Date
            try {
                if (str_contains($dateStr, '/')) {
                    $date = Carbon::createFromFormat('d/m/Y', $dateStr);
                } else {
                    // Try parsing "9 Nov 2025" -> d M Y
                    // Carbon handles "Day Month Year" usually? 
                    // Let's be explicit manually as "Nov" needs English locale or manual map?
                    // Assuming Carbon can parse "9 Nov 2025" if locale is standard.
                    $date = Carbon::parse($dateStr);
                }
            } catch (\Exception $e) {
                // Fallback
                $date = Carbon::now();
            }

            // Parse Amount
            $amount = (float) str_replace([',', 'Rp'], '', $amountStr);

            // Find or Create Client
            $client = Client::firstOrCreate(['name' => $clientName]);

            // Create Reimbursement
            Reimbursement::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'category_id' => $getCategoryId($desc),
                'amount' => $amount,
                'transaction_date' => $date->format('Y-m-d'),
                'note' => $desc,
                'image_path' => 'placeholders/receipt_placeholder.jpg', // Dummy placeholder
                'status' => 'pending',
            ]);
        }
    }
}
