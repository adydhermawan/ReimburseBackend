<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqScanner implements ReceiptScannerInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(?string $modelName = ScannerFactory::DEFAULT_MODEL)
    {
        $this->apiKey = config('services.groq.key');
        $this->model = empty($modelName) ? ScannerFactory::DEFAULT_MODEL : $modelName;
    }

    public function scan(string $imagePath, string $mimeType = 'image/jpeg', array $categories = []): array
    {
        try {
            Log::info("GroqScanner: Scanning image at {$imagePath} with model {$this->model}");

            if (!file_exists($imagePath)) {
                Log::error("GroqScanner: Image file NOT FOUND at {$imagePath}");
                throw new \Exception("Image file not found for scanning: {$imagePath}");
            }

            $rawContents = file_get_contents($imagePath);
            if ($rawContents === false) {
                Log::error("GroqScanner: Failed to read file contents at {$imagePath}");
                throw new \Exception("Failed to read image file contents.");
            }

            $imageData = base64_encode($rawContents);
            Log::info("GroqScanner: Base64 data generated. Length: " . strlen($imageData));

            $categoriesString = implode(', ', $categories);

            $prompt = "Analyze this receipt image. Extract the following information and return ONLY a JSON object with these keys:
- total_amount (number)
- transaction_date (string, YYYY-MM-DD format)
- merchant_name (string)
- category_prediction (string, must be exactly one of: $categoriesString)

If a value is not found, use null. Return raw JSON, no markdown formatting. Do not include a summary.";

            $response = Http::timeout(60)
                ->withToken($this->apiKey)
                ->post($this->baseUrl, [
                    'model' => $this->model,
                    'temperature' => 0,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $prompt],
                                [
                                    'type' => 'image_url',
                                    'image_url' => ['url' => "data:{$mimeType};base64,{$imageData}"],
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error('Groq API Error: ' . $errorBody);
                throw new \Exception('Failed to scan receipt with Groq. Details: ' . $errorBody);
            }

            $textResponse = $response->json('choices.0.message.content') ?? '{}';

            // Clean markdown if present
            $textResponse = str_replace(['```json', '```'], '', $textResponse);

            return json_decode($textResponse, true) ?? [];

        } catch (\Throwable $e) {
            Log::error('Groq Scanner Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
