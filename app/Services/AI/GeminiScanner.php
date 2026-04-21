<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiScanner implements ReceiptScannerInterface
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct(?string $modelName = 'gemma-3-27b-it')
    {
        $this->apiKey = config('services.gemini.key');
        
        $model = empty($modelName) ? 'gemma-3-27b-it' : $modelName;
        $this->baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    public function scan(string $imagePath, string $mimeType = 'image/jpeg', array $categories = []): array
    {
        try {
            Log::info("GeminiScanner: Scanning image at {$imagePath}");
            
            if (!file_exists($imagePath)) {
                Log::error("GeminiScanner: Image file NOT FOUND at {$imagePath}");
                throw new \Exception("Image file not found for scanning: {$imagePath}");
            }

            $rawContents = file_get_contents($imagePath);
            if ($rawContents === false) {
                Log::error("GeminiScanner: Failed to read file contents at {$imagePath}");
                throw new \Exception("Failed to read image file contents.");
            }

            $imageData = base64_encode($rawContents);
            Log::info("GeminiScanner: Base64 data generated. Length: " . strlen($imageData));
            
            $categoriesString = implode(', ', $categories);
            
            $prompt = "Analyze this receipt image. Extract the following information and return ONLY a JSON object with these keys: 
- total_amount (number)
- transaction_date (string, YYYY-MM-DD format)
- merchant_name (string)
- category_prediction (string, must be exactly one of: $categoriesString)

If a value is not found, use null. Return raw JSON, no markdown formatting. Do not include a summary.";

            $response = Http::timeout(60)->post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType, 
                                    'data' => $imageData
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error('Gemini API Error: ' . $errorBody);
                throw new \Exception('Failed to scan receipt with Gemini. Details: ' . $errorBody);
            }

            $json = $response->json();
            
            // Parse Gemini response structure
            $textResponse = $json['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            
            // Clean markdown if present
            $textResponse = str_replace(['```json', '```'], '', $textResponse);
            
            return json_decode($textResponse, true) ?? [];

        } catch (\Throwable $e) {
            Log::error('Gemini Scanner Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
