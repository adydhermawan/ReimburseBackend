<?php

namespace App\Jobs;

use App\Models\Reimbursement;
use App\Models\Category;
use App\Services\AI\GeminiScanner;
use App\Services\AI\GrokScanner;
use App\Services\AI\ReceiptScannerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessReceiptScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reimbursementId;
    public $imagePath;
    public $mimeType;
    public $provider;

    /**
     * Create a new job instance.
     */
    public function __construct(int $reimbursementId, string $imagePath, string $mimeType, string $provider = 'gemini')
    {
        $this->reimbursementId = $reimbursementId;
        $this->imagePath = $imagePath;
        $this->mimeType = $mimeType;
        $this->provider = $provider;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Starting background scan for Reimbursement ID: {$this->reimbursementId}");
            
            $reimbursement = Reimbursement::find($this->reimbursementId);
            if (!$reimbursement) {
                Log::warning("Reimbursement ID {$this->reimbursementId} not found for scanning.");
                return;
            }

            // Get absolute path to the stored image
            $disk = config('filesystems.default');
            if ($disk === 'cloudinary' || str_starts_with($reimbursement->image_path, 'http')) {
                // If using external storage, we might need to download it or pass the URL if scanner supports it.
                // For now, use the temporary local path that was passed if it exists
                $absolutePath = $this->imagePath;
            } else {
                $absolutePath = Storage::path($reimbursement->image_path);
            }

            // Check if file exists to scan
            if (!file_exists($absolutePath)) {
                Log::error("Image file not found for scanning: {$absolutePath}");
                $reimbursement->update(['note' => "Analisa AI gagal: File gambar tidak ditemukan."]);
                return;
            }

            $scanner = $this->getScanner($this->provider);
            $categories = Category::active()->pluck('name')->toArray();

            // Perform scan
            $data = $scanner->scan($absolutePath, $this->mimeType, $categories);
            
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
            
            // Handle client update if finding merchant name
            if (!empty($data['merchant_name'])) {
                // Try to resolve client
                $client = \App\Models\Client::firstOrCreate(
                    ['name' => $data['merchant_name']],
                    [
                        'created_by' => $reimbursement->user_id,
                        'is_auto_registered' => true,
                    ]
                );
                $updateData['client_id'] = $client->id;
            }

            // Handle category update
            if (!empty($data['category_prediction'])) {
                $category = Category::where('name', $data['category_prediction'])->first();
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
            
            Log::info("Successfully updated Reimbursement ID: {$this->reimbursementId} with AI data.");

        } catch (\Exception $e) {
            Log::error("Background Scan failed for Reimbursement ID {$this->reimbursementId}: " . $e->getMessage());
            
            // Re-fetch to ensure we have latest and update note
            $reimbursement = Reimbursement::find($this->reimbursementId);
            if ($reimbursement) {
                // Keep the draft note but append failure
                $note = trim(str_replace('Memproses analisa AI di layar belakang...', '', $reimbursement->note));
                $failureMsg = "Analisa otomatis gagal, silakan isi data secara manual.";
                $reimbursement->update([
                    'note' => $note ? $note . "\n" . $failureMsg : $failureMsg
                ]);
            }
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
