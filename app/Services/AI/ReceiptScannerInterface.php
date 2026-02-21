<?php

namespace App\Services\AI;

interface ReceiptScannerInterface
{
    /**
     * Scan a receipt image and return structured data.
     *
     * @param string $imagePath Path to the image file
     * @return array Structured data (amount, date, merchant, category, summary)
     */
    public function scan(string $imagePath, string $mimeType = 'image/jpeg', array $categories = []): array;
}
