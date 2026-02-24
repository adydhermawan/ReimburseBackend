<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use App\Models\Client;
use App\Jobs\ProcessReceiptScan;
use App\Services\AI\ReceiptScannerInterface;
use App\Services\AI\GeminiScanner;
use App\Services\AI\GrokScanner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ScanReceiptController extends Controller
{
    /**
     * Scan a receipt image.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240', // Max 10MB
        ]);

        try {
            // Get the uploaded file
            $file = $request->file('image');
            $path = $file->getRealPath();

            // Resolve the scanner based on ENV or config
            $provider = config('services.ai.provider', 'gemini');
            $scanner = $this->getScanner($provider);

            // Fetch active categories for AI context
            $categories = \App\Models\Category::active()->pluck('name')->toArray();

            // Scan with categories context
            $mimeType = $file->getMimeType() ?? 'image/jpeg';
            $data = $scanner->scan($path, $mimeType, $categories);

            // Get latest client used by this user
            $latestReimburse = \App\Models\Reimbursement::where('user_id', $request->user()->id)
                ->latest()
                ->first();
            
            // If we have a latest reimbursement, use its client name as default
            // But only if we can resolve that client name
            if ($latestReimburse && $latestReimburse->client) {
                 $data['merchant_name'] = $latestReimburse->client->name; // Override merchant_name with latest client for "Client" field
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'provider' => $provider
            ]);

        } catch (\Exception $e) {
            Log::error('Scan Receipt Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze receipt. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start a background scan and immediately return a draft reimbursement.
     */
    public function draftScan(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240', // Max 10MB
        ]);

        try {
            $user = $request->user();
            $month = now()->format('Y-m');
            $directory = config('filesystems.default') === 'cloudinary' 
                ? "recashy/{$user->id}/{$month}" 
                : 'receipts';
            
            $file = $request->file('image');
            $mimeType = $file->getMimeType() ?? 'image/jpeg';
            $imagePath = $file->store($directory);
            
            // For AI we need absolute path.
            $disk = config('filesystems.default');
            $absolutePath = '';
            if ($disk === 'cloudinary') {
                $tempPath = sys_get_temp_dir() . '/' . uniqid('receipt_') . '.' . $file->getClientOriginalExtension();
                copy($file->getRealPath(), $tempPath);
                $absolutePath = $tempPath;
            } else {
                $absolutePath = \Illuminate\Support\Facades\Storage::path($imagePath);
            }

            // Get latest client to satisfy foreign constraint
            $latestReimburse = Reimbursement::where('user_id', $user->id)->latest()->first();
            $clientId = null;
            
            if ($latestReimburse && $latestReimburse->client_id) {
                $clientId = $latestReimburse->client_id;
            } else {
                $client = Client::firstOrCreate(
                    ['name' => 'Draft Client (Pending AI)'],
                    ['created_by' => $user->id, 'is_auto_registered' => true]
                );
                $clientId = $client->id;
            }
            
            // Create draft reimbursement
            $reimbursement = Reimbursement::create([
                'user_id' => $user->id,
                'client_id' => $clientId,
                'category_name' => 'Uncategorized',
                'amount' => 0,
                'transaction_date' => now()->format('Y-m-d'),
                'note' => 'Memproses analisa AI di layar belakang...',
                'image_path' => $imagePath,
                'status' => Reimbursement::STATUS_PENDING,
            ]);

            // Dispatch background job
            $provider = config('services.ai.provider', 'gemini');
            ProcessReceiptScan::dispatch($reimbursement->id, $absolutePath, $mimeType, $provider)->afterResponse();

            $reimbursement->load(['client:id,name', 'category:id,name,icon']);

            return response()->json([
                'success' => true,
                'message' => 'Draft scan started',
                'data' => $reimbursement,
            ]);

        } catch (\Exception $e) {
            Log::error('Draft Scan Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate draft scan. ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function getScanner(string $provider): ReceiptScannerInterface
    {
        switch ($provider) {
            case 'grok':
                return new GrokScanner();
            case 'gemini':
            default:
                return new GeminiScanner();
        }
    }
}
