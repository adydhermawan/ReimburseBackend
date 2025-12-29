<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    public function check()
    {
        $status = [
            'api' => true,
            'database' => false,
            'cloudinary' => false,
            'message' => 'All systems operational'
        ];

        // Check Database
        try {
            DB::connection()->getPdo();
            $status['database'] = true;
        } catch (\Exception $e) {
            $status['database'] = false;
            $status['message'] = 'Database connection failed: ' . $e->getMessage();
            Log::error('Health Check - Database Error: ' . $e->getMessage());
        }

        // Check Cloudinary
        // We will check if the environment variable is set. 
        // For a more robust check, we could try to list assets if the SDK allows, 
        // but checking the config is a good first step for connectivity potential.
        $cloudinaryUrl = env('CLOUDINARY_URL');
        if (!empty($cloudinaryUrl)) {
             $status['cloudinary'] = true;
        } else {
             $status['cloudinary'] = false;
             if ($status['message'] === 'All systems operational') {
                 $status['message'] = 'Cloudinary URL not configured';
             }
        }

        return response()->json($status);
    }
}
