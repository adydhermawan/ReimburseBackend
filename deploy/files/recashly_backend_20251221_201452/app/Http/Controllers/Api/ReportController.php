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
        // Ensure user owns this report
        if ($report->user_id !== $request->user()->id) {
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
     * Download report PDF.
     */
    public function download(Request $request, Report $report)
    {
        // Ensure user owns this report
        if ($report->user_id !== $request->user()->id) {
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
