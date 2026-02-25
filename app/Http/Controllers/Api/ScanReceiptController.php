<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use App\Models\Client;
use App\Jobs\ProcessReceiptScan;
use App\Services\AI\ReceiptScannerInterface;
use App\Services\AI\GeminiScanner;
use App\Services\AI\GrokScanner;
use App\Services\AI\GroqScanner;
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

            // Get latest client used by this user to prioritize over AI's merchant name
            // Users prefer the Client field to default to their last used one
            $latestReimburse = \App\Models\Reimbursement::where('user_id', $request->user()->id)
                ->latest('id')
                ->first();
            
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
            
            // Save draft reimbursement
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

            $reimbursement->load(['client:id,name', 'category:id,name,icon']);

            return response()->json([
                'success' => true,
                'message' => 'Draft scan created',
                'data' => $reimbursement,
                // We return absolutePath, mimeType, and provider so the client can trigger the process
                'meta' => [
                    'absolute_path' => $absolutePath,
                    'mime_type' => $mimeType,
                    'provider' => config('services.ai.provider', 'gemini')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Draft Scan Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate draft scan. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process AI parsing synchronously. Meant to be called in the background by the frontend client.
     */
    public function processDraft(Request $request, Reimbursement $reimbursement): JsonResponse
    {
        // Ensure user owns reimbursement
        if ($reimbursement->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $absolutePath = $request->input('absolute_path');
        $mimeType = $request->input('mime_type', 'image/jpeg');
        $provider = $request->input('provider', 'gemini');

        try {
            Log::info("Starting sync background scan for Reimbursement ID: {$reimbursement->id}");
            
            // Fallback: On Vercel serverless, the /tmp file from draftScan might not exist anymore.
            // So we check if it exists, and if not, we download it from the cloud URL.
            $disk = config('filesystems.default');
            
            if (!file_exists($absolutePath)) {
                if ($disk === 'cloudinary' && $reimbursement->image_url) {
                    // Try to download from Cloudinary to a new temp file
                    Log::info("Local temp file missing, downloading from Cloudinary: {$reimbursement->image_url}");
                    $tempPath = sys_get_temp_dir() . '/' . uniqid('receipt_dl_') . '.jpg';
                    $downloadResponse = \Illuminate\Support\Facades\Http::get($reimbursement->image_url);
                    
                    if ($downloadResponse->successful() && $downloadResponse->body()) {
                        file_put_contents($tempPath, $downloadResponse->body());
                        $absolutePath = $tempPath;
                    } else {
                        Log::error("Failed to download image from Cloudinary HTTP " . $downloadResponse->status());
                        $reimbursement->update(['note' => "Analisa AI gagal: Tidak dapat mengakses gambar dari penyimpanan cloud."]);
                        return response()->json(['success' => false, 'message' => 'Failed to download image from cloud storage'], 500);
                    }
                } else if ($disk === 'local' || $disk === 'public') {
                    $absolutePath = \Illuminate\Support\Facades\Storage::path($reimbursement->image_path);
                    if (!file_exists($absolutePath)) {
                        $reimbursement->update(['note' => "Analisa AI gagal: File gambar tidak ditemukan di local storage."]);
                        return response()->json(['success' => false, 'message' => 'Image file not found locally'], 404);
                    }
                } else {
                    $reimbursement->update(['note' => "Analisa AI gagal: File gambar tidak ditemukan."]);
                    return response()->json(['success' => false, 'message' => 'Image file not found'], 404);
                }
            }

            $scanner = $this->getScanner($provider);
            $categories = \App\Models\Category::active()->pluck('name')->toArray();

            // Perform scan with fallback mechanism
            try {
                $data = $scanner->scan($absolutePath, $mimeType, $categories);
            } catch (\Throwable $e) {
                // If the primary provider (e.g. Gemini) fails, try Groq as an ultimate fallback
                if ($provider !== 'groq') {
                    Log::warning("Primary AI Scanner ({$provider}) failed. Falling back to Groq. Reason: " . $e->getMessage());
                    $fallbackScanner = $this->getScanner('groq');
                    
                    try {
                        $data = $fallbackScanner->scan($absolutePath, $mimeType, $categories);
                    } catch (\Throwable $fallbackE) {
                        throw $fallbackE;
                    }
                } else {
                    throw $e; // If it was already using Groq, bubble the exception up
                }
            }
            
            // Clean up temporary local file if we used Cloudinary
            if ($disk === 'cloudinary' && file_exists($absolutePath) && strpos($absolutePath, 'tmp') !== false) {
                @unlink($absolutePath);
            }

            // Prepare update data
            $updateData = [];
            
            if (!empty($data['total_amount'])) {
                $updateData['amount'] = $data['total_amount'];
            }
            
            if (!empty($data['transaction_date'])) {
                $updateData['transaction_date'] = $data['transaction_date'];
            }
            
            // Handle client update if finding merchant name OR if we have a default latest client
            // The user prefers the 'Client' field to default to the latest reimbursement's client
            $latestReimburse = \App\Models\Reimbursement::where('user_id', $reimbursement->user_id)
                ->where('id', '!=', $reimbursement->id)
                ->latest('id')
                ->first();

            if ($latestReimburse && $latestReimburse->client_id) {
                $updateData['client_id'] = $latestReimburse->client_id;
            } else if (!empty($data['merchant_name'])) {
                $client = Client::firstOrCreate(
                    ['name' => $data['merchant_name']],
                    ['created_by' => $reimbursement->user_id, 'is_auto_registered' => true]
                );
                $updateData['client_id'] = $client->id;
            }

            // Handle category update
            if (!empty($data['category_prediction'])) {
                $category = \App\Models\Category::where('name', $data['category_prediction'])->first();
                if ($category) {
                    $updateData['category_id'] = $category->id;
                    $updateData['category_name'] = null;
                } else {
                    $updateData['category_name'] = $data['category_prediction'];
                }
            }

            // Remove the draft note
            $updateData['note'] = null;

            // Apply updates
            $reimbursement->update($updateData);
            Log::info("Successfully updated Reimbursement ID: {$reimbursement->id} with AI data.");

            return response()->json([
                'success' => true,
                'message' => 'AI Processing complete',
                'data' => $reimbursement->fresh(['client', 'category'])
            ]);

        } catch (\Throwable $e) {
            Log::error("Background Scan failed for Reimbursement ID {$reimbursement->id}: " . $e->getMessage());
            
            $note = trim(str_replace('Memproses analisa AI di layar belakang...', '', $reimbursement->note));
            $failureMsg = "Analisa otomatis gagal: " . $e->getMessage() . " | Silakan isi data secara manual.";
            $reimbursement->update([
                'note' => $note ? $note . "\n" . $failureMsg : $failureMsg
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AI Processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function getScanner(string $provider): ReceiptScannerInterface
    {
        switch ($provider) {
            case 'grok':
                return new GrokScanner();
            case 'groq':
                return new GroqScanner();
            case 'gemini':
            default:
                return new GeminiScanner();
        }
    }
}
