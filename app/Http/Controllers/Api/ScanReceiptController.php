<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
