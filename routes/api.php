<?php

use App\Http\Controllers\Api\ScanReceiptController;
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

Route::get('/debug-upload-methods', function () {
    $results = [];
    $disk = config('filesystems.default'); 

    // Method 1: Simple String
    try {
        $start = microtime(true);
        Illuminate\Support\Facades\Storage::put('debug_string.txt', 'Simple string content');
        $results['method_1_string'] = ['success' => true, 'time' => microtime(true) - $start, 'url' => Illuminate\Support\Facades\Storage::url('debug_string.txt')];
    } catch (\Throwable $e) {
        $results['method_1_string'] = ['success' => false, 'error' => $e->getMessage()];
    }

    // Method 2: Stream (Text)
    try {
        $start = microtime(true);
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, 'Stream content');
        rewind($stream);
        Illuminate\Support\Facades\Storage::put('debug_stream.txt', $stream);
        if (is_resource($stream)) fclose($stream);
        $results['method_2_stream_text'] = ['success' => true, 'time' => microtime(true) - $start, 'url' => Illuminate\Support\Facades\Storage::url('debug_stream.txt')];
    } catch (\Throwable $e) {
        $results['method_2_stream_text'] = ['success' => false, 'error' => $e->getMessage()];
    }

    // Method 3: Stream (Binary/PDF header)
    try {
        $start = microtime(true);
        $content = "%PDF-1.4\n%\n1 0 obj\n<<\n/Type /Catalog\n>>\nendobj"; 
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);
        Illuminate\Support\Facades\Storage::put('debug_stream_binary.pdf', $stream);
        if (is_resource($stream)) fclose($stream);
        $results['method_3_stream_binary'] = ['success' => true, 'time' => microtime(true) - $start, 'url' => Illuminate\Support\Facades\Storage::url('debug_stream_binary.pdf')];
    } catch (\Throwable $e) {
        $results['method_3_stream_binary'] = ['success' => false, 'error' => $e->getMessage()];
    }

    // Method 4: Temp File (putFile)
    try {
        $start = microtime(true);
        $tempFile = stream_get_meta_data(tmpfile())['uri'];
        file_put_contents($tempFile, 'Temp file content');
        $path = Illuminate\Support\Facades\Storage::putFile('debug_uploaded_files', new \Illuminate\Http\File($tempFile));
        $results['method_4_putFile'] = ['success' => true, 'time' => microtime(true) - $start, 'url' => Illuminate\Support\Facades\Storage::url($path)];
    } catch (\Throwable $e) {
        $results['method_4_putFile'] = ['success' => false, 'error' => $e->getMessage()];
    }

     // Method 5: File Handle
    try {
        $start = microtime(true);
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempFile, "%PDF-1.4\nFake PDF Content");
        $handle = fopen($tempFile, 'r');
        Illuminate\Support\Facades\Storage::put('debug_file_handle.pdf', $handle);
        if (is_resource($handle)) fclose($handle);
        $results['method_5_file_handle'] = ['success' => true, 'time' => microtime(true) - $start, 'url' => Illuminate\Support\Facades\Storage::url('debug_file_handle.pdf')];
    } catch (\Throwable $e) {
        $results['method_5_file_handle'] = ['success' => false, 'error' => $e->getMessage()];
    }

    return response()->json([
        'default_disk' => $disk,
        'config_dump' => config("filesystems.disks.{$disk}"),
        'results' => $results
    ]);
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

    // AI Receipt Scanning
    Route::post('/scan-receipt', [ScanReceiptController::class, 'store']);
    Route::post('/reimbursements/draft-scan', [ScanReceiptController::class, 'draftScan']);
    Route::post('/reimbursements/{reimbursement}/process-ai', [ScanReceiptController::class, 'processDraft']);

    // App Version Management (admin only)
    Route::post('/app-version', [AppVersionController::class, 'store']);
});

// Internal routes for Next.js Admin Panel
Route::prefix('internal')->group(function () {
    Route::middleware([\App\Http\Middleware\VerifyInternalSecret::class])->group(function () {
        Route::post('/reports/{report}/generate', [ReportController::class, 'generate']);
    });
});
