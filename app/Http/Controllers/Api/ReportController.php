<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Get user's reports.
     */
    public function index(Request $request): JsonResponse
    {
        $reports = Report::where('user_id', $request->user()->id)
            ->orderBy('period_start', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Get single report detail.
     */
    public function show(Request $request, Report $report): JsonResponse
    {
        // Allow if owner OR admin
        if ($report->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan',
            ], 403);
        }

        // Load relationships
        $report->load(['reimbursements.client', 'reimbursements.category']);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Generate PDF for a report.
     */
    public function generate(Request $request, Report $report, \App\Services\PdfReportService $service): JsonResponse
    {
        // Allow if owner OR admin
        if ($report->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $path = $service->generate($report);
            
            return response()->json([
                'success' => true,
                'message' => 'PDF Generated successfully',
                'data' => [
                    'pdf_path' => $path,
                    'pdf_url' => url('storage/' . $path)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark report as paid.
     */
    public function markAsPaid(Request $request, Report $report): JsonResponse
    {
        // Only Admin can mark as paid
        if (!$request->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - Admin only'], 403);
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
        ]);

        $report->update([
            'status' => 'paid',
            'payment_date' => $validated['payment_date'],
        ]);

        // Update all reimbursements
        $report->reimbursements()->update(['status' => 'paid']);

        return response()->json([
            'success' => true,
            'message' => 'Report marked as paid',
            'data' => $report
        ]);
    }

    /**
     * Download report PDF.
     */
    public function download(Request $request, Report $report)
    {
        // Allow if owner OR admin
        if ($report->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan',
            ], 403);
        }

        if (!$report->pdf_path || !Storage::disk('public')->exists($report->pdf_path)) {
            return response()->json([
                'success' => false,
                'message' => 'PDF belum tersedia',
            ], 404);
        }

        $filename = 'Reimburse_' . $report->period_start->format('Y-m') . '.pdf';

        return Storage::disk('public')->download($report->pdf_path, $filename);
    }
}
