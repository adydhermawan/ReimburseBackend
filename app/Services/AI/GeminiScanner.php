<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiScanner implements ReceiptScannerInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    public function scan(string $imagePath, string $mimeType = 'image/jpeg', array $categories = []): array
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            
            $categoriesString = implode(', ', $categories);
            
            $prompt = "Analyze this receipt image. Extract the following information and return ONLY a JSON object with these keys: 
- total_amount (number)
- transaction_date (string, YYYY-MM-DD format)
- merchant_name (string)
- category_prediction (string, must be exactly one of: $categoriesString)

If a value is not found, use null. Return raw JSON, no markdown formatting. Do not include a summary.";

            $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => 'You are an expert receipt data extractor. Always return JSON.']
                    ]
                ],
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
                ],
                'generationConfig' => [
                    'temperature' => 0.0,
                    'maxOutputTokens' => 300,
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->body());
                throw new \Exception('Failed to scan receipt with Gemini.');
            }

            $json = $response->json();
            
            // Parse Gemini response structure
            $textResponse = $json['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            
            // Clean markdown if present
            $textResponse = str_replace(['```json', '```'], '', $textResponse);
            
            return json_decode($textResponse, true) ?? [];

        } catch (\Exception $e) {
            Log::error('Gemini Scanner Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
