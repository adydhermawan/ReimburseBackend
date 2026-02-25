<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqScanner implements ReceiptScannerInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key'); // Needs to be added to services.php
    }

    public function scan(string $imagePath, string $mimeType = 'image/jpeg', array $categories = []): array
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            $categoriesString = implode(', ', $categories);
            
            $messages = [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Analyze this receipt image. Extract the following information and return ONLY a JSON object with these keys: 
                            - total_amount (number)
                            - transaction_date (string, YYYY-MM-DD format)
                            - merchant_name (string)
                            - category_prediction (string, must be exactly one of: $categoriesString)
                            
                            If a value is not found, use null. do not include markdown formatting. Return raw JSON."
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$imageData}"
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::timeout(15)->withToken($this->apiKey)->post($this->baseUrl, [
                'model' => 'qwen/qwen3-32b', // User requested to test this model
                'messages' => $messages,
                'temperature' => 0.1,
                'stream' => false,
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->failed()) {
                Log::error('Groq API Error: ' . $response->body());
                throw new \Exception('Failed to scan receipt with Groq. Details: ' . $response->body());
            }

            $json = $response->json();
            $content = $json['choices'][0]['message']['content'] ?? '{}';
            
            // Clean markdown
            $content = str_replace(['```json', '```'], '', $content);
            
            return json_decode($content, true) ?? [];

        } catch (\Throwable $e) {
            Log::error('Groq Scanner Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
