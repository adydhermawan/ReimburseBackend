<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GrokScanner implements ReceiptScannerInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.x.ai/v1/chat/completions'; // xAI API Endpoint

    public function __construct()
    {
        $this->apiKey = config('services.grok.key');
    }

    public function scan(string $imagePath, string $mimeType = 'image/jpeg', array $categories = []): array
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            
            $messages = [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Analyze this receipt image. Extract the following information and return ONLY a JSON object with these keys: 
                            - total_amount (number)
                            - transaction_date (string, YYYY-MM-DD)
                            - merchant_name (string)
                            - category_prediction (string)
                            - summary (string)
                            
                            If a value is not found, use null. do not include markdown formatting."
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:image/jpeg;base64,{$imageData}",
                                'detail' => 'high'
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::withToken($this->apiKey)->post($this->baseUrl, [
                'model' => 'grok-2-vision-1212', // Or latest available vision model
                'messages' => $messages,
                'temperature' => 0.1,
                'stream' => false
            ]);

            if ($response->failed()) {
                Log::error('Grok API Error: ' . $response->body());
                throw new \Exception('Failed to scan receipt with Grok.');
            }

            $json = $response->json();
            $content = $json['choices'][0]['message']['content'] ?? '{}';
            
            // Clean markdown
            $content = str_replace(['```json', '```'], '', $content);
            
            return json_decode($content, true) ?? [];

        } catch (\Exception $e) {
            Log::error('Grok Scanner Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
