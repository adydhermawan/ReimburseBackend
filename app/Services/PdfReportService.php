<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Storage;
use FPDF;

class PdfReportService
{
    // Color constants matching the existing template
    private const COLOR_PRIMARY = [0, 188, 212];       // #00bcd4 - cyan
    private const COLOR_PRIMARY_DARK = [0, 131, 143];  // #00838f
    private const COLOR_WHITE = [255, 255, 255];
    private const COLOR_BLACK = [51, 51, 51];           // #333
    private const COLOR_GRAY = [85, 85, 85];             // #555
    private const COLOR_LIGHT_GRAY = [136, 136, 136];    // #888
    private const COLOR_BORDER = [221, 221, 221];        // #ddd
    private const COLOR_BG_LIGHT = [245, 245, 245];      // #f5f5f5
    private const COLOR_BG_CYAN = [224, 247, 250];       // #e0f7fa
    private const COLOR_EVEN_ROW = [249, 249, 249];      // #f9f9f9

    /**
     * Generate PDF for a report using FPDF (no GD extension required).
     */
    public function generate(Report $report): string
    {
        $report->load(['user', 'reimbursements.client', 'reimbursements.category']);

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(15, 15, 15);

        $this->renderDataPage($pdf, $report);
        $this->renderImagePages($pdf, $report);

        // Generate filename
        $filename = sprintf(
            'recashy/%s/report/%s_%s.pdf',
            $report->user->id,
            $report->period_start->format('Ym'),
            $report->id
        );
        $filename = preg_replace('/[^a-zA-Z0-9_\/.-]/', '_', $filename);

        // Save PDF to temp file, then to storage
        $tempPath = tempnam(sys_get_temp_dir(), 'report_');
        $pdf->Output('F', $tempPath);

        $directory = dirname($filename);
        $name = basename($filename);
        Storage::putFileAs($directory, new \Illuminate\Http\File($tempPath), $name);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        $report->update([
            'pdf_path' => $filename,
            'status' => 'generated',
        ]);

        return $filename;
    }

    /**
     * Render Page 1: Data table with header, user info, and summary.
     */
    private function renderDataPage(FPDF $pdf, Report $report): void
    {
        $pdf->AddPage();
        $pageWidth = 210;
        $margin = 15;
        $contentWidth = $pageWidth - ($margin * 2);
        $y = $margin;

        // --- Header line ---
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->Rect($margin, $y, $contentWidth, 0.8, 'F');

        // --- Title ---
        $y += 3;
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(...self::COLOR_PRIMARY_DARK);
        $pdf->SetXY($margin, $y);
        $pdf->Cell($contentWidth, 8, 'LAPORAN REIMBURSEMENT', 0, 1, 'C');

        // --- Period ---
        $y += 10;
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(...self::COLOR_GRAY);
        $periodText = 'Periode: ' . $report->period_start->format('d F Y') . ' - ' . $report->period_end->format('d F Y');
        $pdf->SetXY($margin, $y);
        $pdf->Cell($contentWidth, 6, $this->utf8($periodText), 0, 1, 'C');

        // --- Header line ---
        $y += 8;
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->Rect($margin, $y, $contentWidth, 0.8, 'F');

        // --- User Info Box ---
        $y += 5;
        $boxHeight = 20;
        $pdf->SetFillColor(...self::COLOR_BG_LIGHT);
        $pdf->Rect($margin, $y, $contentWidth, $boxHeight, 'F');

        $infoY = $y + 3;
        $halfWidth = $contentWidth / 2;

        // Row 1: Nama + Tanggal Cetak
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor(...self::COLOR_BLACK);
        $pdf->SetXY($margin + 5, $infoY);
        $pdf->Cell(22, 5, 'Nama:', 0, 0);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Cell($halfWidth - 30, 5, $this->utf8($report->user->name), 0, 0);

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY($margin + $halfWidth, $infoY);
        $pdf->Cell(30, 5, 'Tanggal Cetak:', 0, 0);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Cell(50, 5, now()->format('d F Y H:i'), 0, 0);

        // Row 2: Email + Status
        $infoY += 8;
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY($margin + 5, $infoY);
        $pdf->Cell(22, 5, 'Email:', 0, 0);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Cell($halfWidth - 30, 5, $report->user->email, 0, 0);

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY($margin + $halfWidth, $infoY);
        $pdf->Cell(30, 5, 'Status:', 0, 0);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Cell(50, 5, ucfirst($report->status), 0, 0);

        // --- Data Table ---
        $y += $boxHeight + 5;

        $columns = [
            ['header' => 'No', 'width' => 10, 'align' => 'C'],
            ['header' => 'Tanggal', 'width' => 22, 'align' => 'C'],
            ['header' => 'Client', 'width' => 35, 'align' => 'L'],
            ['header' => 'Kategori', 'width' => 28, 'align' => 'L'],
            ['header' => 'Keterangan', 'width' => 50, 'align' => 'L'],
            ['header' => 'Jumlah', 'width' => 35, 'align' => 'R'],
        ];

        $rowHeight = 7;

        // Table header
        $pdf->SetFillColor(...self::COLOR_PRIMARY);
        $pdf->SetTextColor(...self::COLOR_WHITE);
        $pdf->SetFont('Helvetica', 'B', 8);

        $x = $margin;
        foreach ($columns as $col) {
            $pdf->SetXY($x, $y);
            $pdf->Cell($col['width'], 8, strtoupper($col['header']), 1, 0, 'C', true);
            $x += $col['width'];
        }
        $y += 8;

        // Table rows
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(...self::COLOR_BLACK);

        foreach ($report->reimbursements as $index => $item) {
            // Check page break
            if ($y + $rowHeight > 265) {
                $this->renderFooter($pdf);
                $pdf->AddPage();
                $y = $margin;
            }

            // Alternate row background
            if ($index % 2 === 0) {
                $pdf->SetFillColor(...self::COLOR_EVEN_ROW);
                $pdf->Rect($margin, $y, $contentWidth, $rowHeight, 'F');
            }

            $rowData = [
                (string) ($index + 1),
                $item->transaction_date->format('d/m/Y'),
                $this->truncate($item->client->name ?? '-', 18),
                $this->truncate($item->category->name ?? ($item->category_name ?? '-'), 14),
                $this->truncate($item->note ?: '-', 26),
                'Rp ' . number_format($item->amount, 0, ',', '.'),
            ];

            $pdf->SetDrawColor(...self::COLOR_BORDER);
            $x = $margin;
            foreach ($columns as $colIndex => $col) {
                $pdf->SetXY($x, $y);
                $pdf->Cell($col['width'], $rowHeight, $this->utf8($rowData[$colIndex]), 1, 0, $col['align']);
                $x += $col['width'];
            }

            $y += $rowHeight;
        }

        // --- Summary Box ---
        $y += 5;
        if ($y + 18 > 270) {
            $this->renderFooter($pdf);
            $pdf->AddPage();
            $y = $margin;
        }

        $pdf->SetFillColor(...self::COLOR_BG_CYAN);
        $pdf->Rect($margin, $y, $contentWidth, 16, 'F');

        $pdf->SetTextColor(...self::COLOR_BLACK);
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetXY($margin + 5, $y + 4);
        $pdf->Cell(30, 8, 'Total Entry:', 0, 0);
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->Cell(30, 8, $report->entry_count . ' item', 0, 0);

        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetXY($margin + $halfWidth, $y + 4);
        $pdf->Cell(30, 8, 'Total Jumlah:', 0, 0);
        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->SetTextColor(...self::COLOR_PRIMARY_DARK);
        $totalFormatted = 'Rp ' . number_format($report->total_amount, 0, ',', '.');
        $pdf->Cell($halfWidth - 35, 8, $this->utf8($totalFormatted), 0, 0, 'R');

        // Footer
        $this->renderFooter($pdf);
    }

    /**
     * Render receipt image pages (4 images per page in 2x2 grid).
     */
    private function renderImagePages(FPDF $pdf, Report $report): void
    {
        $reimbursements = $report->reimbursements;
        if ($reimbursements->isEmpty()) {
            return;
        }

        $margin = 15;
        $pageWidth = 210;
        $contentWidth = $pageWidth - ($margin * 2);
        $imagesPerPage = 4;
        $chunks = $reimbursements->chunk($imagesPerPage);
        $totalPages = $chunks->count();

        foreach ($chunks as $pageIndex => $chunk) {
            $pdf->AddPage();
            $y = $margin;

            // Page title
            $pdf->SetFont('Helvetica', 'B', 12);
            $pdf->SetTextColor(...self::COLOR_PRIMARY_DARK);
            $pdf->SetXY($margin, $y);
            $title = 'Bukti Transaksi (Halaman ' . ($pageIndex + 1) . ' dari ' . $totalPages . ')';
            $pdf->Cell($contentWidth, 8, $this->utf8($title), 0, 1, 'C');

            $y += 10;
            $pdf->SetFillColor(...self::COLOR_PRIMARY);
            $pdf->Rect($margin, $y, $contentWidth, 0.5, 'F');
            $y += 4;

            // 2x2 grid
            $cellWidth = ($contentWidth - 6) / 2;
            $cellHeight = 115;

            foreach ($chunk->values() as $i => $item) {
                $col = $i % 2;
                $row = intdiv($i, 2);
                $cellX = $margin + $col * ($cellWidth + 6);
                $cellY = $y + $row * ($cellHeight + 6);

                // Cell border
                $pdf->SetDrawColor(...self::COLOR_BORDER);
                $pdf->Rect($cellX, $cellY, $cellWidth, $cellHeight);

                // Try to load and embed image
                $imageEmbedded = false;
                if ($item->image_url) {
                    $imageEmbedded = $this->embedImage($pdf, $item->image_url, $cellX + 4, $cellY + 4, $cellWidth - 8, $cellHeight - 30);
                }

                if (!$imageEmbedded) {
                    // Placeholder
                    $pdf->SetFillColor(...self::COLOR_BG_LIGHT);
                    $pdf->Rect($cellX + 4, $cellY + 4, $cellWidth - 8, $cellHeight - 30, 'F');
                    $pdf->SetTextColor(...self::COLOR_LIGHT_GRAY);
                    $pdf->SetFont('Helvetica', '', 9);
                    $pdf->SetXY($cellX + 4, $cellY + ($cellHeight - 30) / 2);
                    $pdf->Cell($cellWidth - 8, 5, 'Image not available', 0, 0, 'C');
                }

                // Info section below image
                $infoY = $cellY + $cellHeight - 24;
                $pdf->SetDrawColor(...self::COLOR_EVEN_ROW);
                $pdf->Line($cellX + 4, $infoY, $cellX + $cellWidth - 4, $infoY);

                $pdf->SetTextColor(...self::COLOR_BLACK);
                $pdf->SetFont('Helvetica', 'B', 8);
                $dateClient = $item->transaction_date->format('d/m/Y') . ' - ' . ($item->client->name ?? '-');
                $pdf->SetXY($cellX + 5, $infoY + 2);
                $pdf->Cell($cellWidth - 10, 5, $this->utf8($this->truncate($dateClient, 30)), 0, 0);

                $categoryAmount = ($item->category->name ?? '-') . ': Rp ' . number_format($item->amount, 0, ',', '.');
                $pdf->SetFont('Helvetica', '', 7);
                $pdf->SetXY($cellX + 5, $infoY + 8);
                $pdf->Cell($cellWidth - 10, 5, $this->utf8($categoryAmount), 0, 0);
            }

            $this->renderFooter($pdf);
        }
    }

    /**
     * Embed an image from URL into the PDF.
     * Downloads to temp file first, then uses FPDF's native Image() method.
     * FPDF supports JPEG and PNG without GD extension.
     */
    private function embedImage(FPDF $pdf, string $url, float $x, float $y, float $maxWidth, float $maxHeight): bool
    {
        $tempPath = null;
        try {
            // Download image to temp file
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'RecashlyPDF/1.0',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $imageData = @file_get_contents($url, false, $context);
            if ($imageData === false || strlen($imageData) < 100) {
                return false;
            }

            // Detect image type from content
            $type = $this->detectImageType($imageData);
            if (!$type || !in_array($type, ['jpeg', 'png'])) {
                // Skip unsupported formats (GIF, WebP need GD)
                return false;
            }

            $ext = ($type === 'jpeg') ? '.jpg' : '.png';
            $tempPath = tempnam(sys_get_temp_dir(), 'img_') . $ext;
            file_put_contents($tempPath, $imageData);

            // Get image dimensions to calculate proportional sizing
            $imgInfo = @getimagesize($tempPath);
            if ($imgInfo === false) {
                // getimagesize may fail without GD for some formats, but works for basic JPEG/PNG
                // Fallback: use max dimensions
                $pdf->Image($tempPath, $x, $y, $maxWidth, $maxHeight, strtoupper($type));
            } else {
                $imgWidth = $imgInfo[0];
                $imgHeight = $imgInfo[1];

                // Calculate proportional fit
                $ratio = min($maxWidth / $imgWidth, $maxHeight / $imgHeight);
                $drawWidth = $imgWidth * $ratio;
                $drawHeight = $imgHeight * $ratio;

                // Center the image
                $offsetX = $x + ($maxWidth - $drawWidth) / 2;
                $offsetY = $y + ($maxHeight - $drawHeight) / 2;

                $pdf->Image($tempPath, $offsetX, $offsetY, $drawWidth, $drawHeight, strtoupper($type));
            }

            return true;
        } catch (\Exception $e) {
            // Silently fail - image just won't appear
            return false;
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Detect image type from binary content.
     */
    private function detectImageType(string $data): ?string
    {
        // JPEG: starts with FF D8 FF
        if (substr($data, 0, 3) === "\xFF\xD8\xFF") {
            return 'jpeg';
        }
        // PNG: starts with 89 50 4E 47
        if (substr($data, 0, 4) === "\x89PNG") {
            return 'png';
        }
        // GIF: starts with GIF
        if (substr($data, 0, 3) === 'GIF') {
            return 'gif'; // Will be rejected (needs GD)
        }
        return null;
    }

    /**
     * Render page footer.
     */
    private function renderFooter(FPDF $pdf): void
    {
        $pageHeight = 297;
        $margin = 15;
        $pageWidth = 210;
        $contentWidth = $pageWidth - ($margin * 2);

        $pdf->SetDrawColor(...self::COLOR_BORDER);
        $pdf->Line($margin, $pageHeight - 15, $pageWidth - $margin, $pageHeight - 15);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetTextColor(...self::COLOR_LIGHT_GRAY);
        $pdf->SetXY($margin, $pageHeight - 13);
        $pdf->Cell($contentWidth, 5, 'Generated by Reimburse System | ' . config('app.name'), 0, 0, 'C');
    }

    /**
     * Truncate string to max length.
     */
    private function truncate(string $text, int $maxChars): string
    {
        if (mb_strlen($text) > $maxChars) {
            return mb_substr($text, 0, $maxChars - 1) . '..';
        }
        return $text;
    }

    /**
     * Convert UTF-8 text to ISO-8859-1 for FPDF compatibility.
     */
    private function utf8(string $text): string
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text) ?: $text;
    }

    /**
     * Get the PDF download path.
     */
    public function getDownloadPath(Report $report): ?string
    {
        if (!$report->pdf_path) {
            return null;
        }

        return null; // Local path not supported on cloud storage
    }
}
