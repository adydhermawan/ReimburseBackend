<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportPdfDataController extends Controller
{
    /**
     * Return report data formatted for client-side PDF generation.
     * Used by the Filament admin panel's JavaScript PDF generator.
     */
    public function __invoke(Request $request, Report $report): JsonResponse
    {
        // Ensure user is authenticated via web session (Filament)
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Only allow admin or report owner
        $user = auth()->user();
        if (!$user->isAdmin() && $report->user_id !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $report->load(['user', 'reimbursements.client', 'reimbursements.category']);

        $reimbursements = $report->reimbursements->map(function ($item) {
            return [
                'transaction_date' => $item->transaction_date->format('d/m/Y'),
                'client_name' => $item->client->name ?? '-',
                'category_name' => $item->category->name ?? ($item->category_name ?? '-'),
                'note' => $item->note ?: '-',
                'amount' => $item->amount,
                'amount_formatted' => 'Rp ' . number_format($item->amount, 0, ',', '.'),
                'image_url' => $item->image_url,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'report' => [
                    'id' => $report->id,
                    'period_label' => $report->period_label,
                    'period_start' => $report->period_start->format('d F Y'),
                    'period_end' => $report->period_end->format('d F Y'),
                    'status' => ucfirst($report->status),
                    'total_amount' => $report->total_amount,
                    'total_amount_formatted' => 'Rp ' . number_format($report->total_amount, 0, ',', '.'),
                    'entry_count' => $report->entry_count,
                ],
                'user' => [
                    'name' => $report->user->name,
                    'email' => $report->user->email,
                ],
                'reimbursements' => $reimbursements,
                'generated_at' => now()->format('d F Y H:i'),
                'app_name' => config('app.name'),
            ],
        ]);
    }
}
