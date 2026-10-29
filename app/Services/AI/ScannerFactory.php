<?php

namespace App\Services\AI;

class ScannerFactory
{
    /**
     * Default receipt scanning model (Groq). Gemini models are kept as secondary choices.
     */
    public const DEFAULT_MODEL = 'qwen/qwen3.8-27b';

    /**
     * Resolve the scanner for a model id: "gemini-*" goes to Gemini, everything else to Groq.
     */
    public static function make(?string $modelName = null): ReceiptScannerInterface
    {
        $model = empty($modelName) ? self::DEFAULT_MODEL : $modelName;

        if (str_starts_with($model, 'gemini')) {
            return new GeminiScanner($model);
        }

        return new GroqScanner($model);
    }
}
