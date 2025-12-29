<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ReimbursementController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AppVersionController;
use App\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no auth required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/debug-cloudinary', function () {
    try {
        $disk = config('filesystems.default');
        $config = config("filesystems.disks.{$disk}");
        
        // Attempt upload
        $filename = 'debug_' . time() . '.txt';
        $content = 'Debug content ' . date('Y-m-d H:i:s');
        $path = Illuminate\Support\Facades\Storage::disk($disk)->put($filename, $content);
        $url = Illuminate\Support\Facades\Storage::disk($disk)->url($filename);
        
        return response()->json([
            'success' => true,
            'disk' => $disk,
            'config' => $config,
            'url' => $url,
            'env_check' => [
                'FILESYSTEM_DISK' => env('FILESYSTEM_DISK'),
                'CLOUDINARY_URL_SET' => !empty(env('CLOUDINARY_URL')),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'config_dump' => config('filesystems'),
        ], 500);
    }
});

Route::get('/test-connection', function () {
    return response()->json(['success' => true, 'message' => 'API Connection Established', 'ip' => request()->ip()]);
});

Route::get('/health-check', [HealthController::class, 'check']);


// Public categories (can be cached)
Route::get('/categories', [CategoryController::class, 'index']);

// Public app version check (for auto-update)
Route::get('/app-version', [AppVersionController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Clients (for autocomplete)
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);

    // Reimbursements
    Route::get('/reimbursements', [ReimbursementController::class, 'index']);
    Route::post('/reimbursements', [ReimbursementController::class, 'store']);
    Route::get('/reimbursements/summary', [ReimbursementController::class, 'summary']);
    Route::get('/reimbursements/{reimbursement}', [ReimbursementController::class, 'show']);
    Route::put('/reimbursements/{reimbursement}', [ReimbursementController::class, 'update']);
    Route::delete('/reimbursements/{reimbursement}', [ReimbursementController::class, 'destroy']);

    // Reports
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/reports/{report}', [ReportController::class, 'show']);
    Route::get('/reports/{report}/download', [ReportController::class, 'download']);
    Route::post('/reports/{report}/generate', [ReportController::class, 'generate']);
    Route::post('/reports/{report}/paid', [ReportController::class, 'markAsPaid']);

    // App Version Management (admin only)
    Route::post('/app-version', [AppVersionController::class, 'store']);
});

// Internal routes for Next.js Admin Panel
Route::prefix('internal')->group(function () {
    Route::middleware([\App\Http\Middleware\VerifyInternalSecret::class])->group(function () {
        Route::post('/reports/{report}/generate', [ReportController::class, 'generate']);
    });
});
