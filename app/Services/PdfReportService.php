<?php

namespace App\Services;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfReportService
{
    /**
     * Generate PDF for a report
     */
    public function generate(Report $report): string
    {
        $report->load(['user', 'reimbursements.client', 'reimbursements.category']);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.report', [
            'report' => $report,
            'reimbursements' => $report->reimbursements,
        ]);

        // Configure PDF settings
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);

        // Generate filename with dynamic folder: recashy/{user_id}/report/
        $filename = sprintf(
            'recashy/%s/report/%s_%s.pdf',
            $report->user->id,
            $report->period_start->format('Ym'),
            $report->id
        );

        // Clean filename (remove spaces and special chars)
        $filename = preg_replace('/[^a-zA-Z0-9_\/.-]/', '_', $filename);

        // Save to storage using temp file and putFileAs (proven to work)
        $tempPath = tempnam(sys_get_temp_dir(), 'report_');
        file_put_contents($tempPath, $pdf->output());

        $directory = dirname($filename);
        $name = basename($filename);

        Storage::putFileAs($directory, new \Illuminate\Http\File($tempPath), $name);

        // Cleanup
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        // Update report record
        $report->update([
            'pdf_path' => $filename,
            'status' => 'generated',
        ]);

        return $filename;
    }

    /**
     * Get the PDF download path
     */
    public function getDownloadPath(Report $report): ?string
    {
        if (!$report->pdf_path) {
            return null;
        }

        return null; // Local path not supported on cloud storage
    }
}
