<?php

namespace App\Filament\Widgets;

use App\Models\Reimbursement;
use App\Models\Report;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $isAdmin = auth()->user()->isAdmin();
        $userId = auth()->id();

        // Current month stats
        $currentMonthQuery = Reimbursement::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
        
        if (!$isAdmin) {
            $currentMonthQuery->where('user_id', $userId);
        }
        
        $currentMonthTotal = $currentMonthQuery->sum('amount');

        $lastMonthQuery = Reimbursement::whereMonth('transaction_date', $lastMonth->month)
            ->whereYear('transaction_date', $lastMonth->year);
        
        if (!$isAdmin) {
            $lastMonthQuery->where('user_id', $userId);
        }
        
        $lastMonthTotal = $lastMonthQuery->sum('amount');

        $totalTrend = $lastMonthTotal > 0
            ? round((($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1)
            : 0;

        // Entry counts
        $entriesQuery = Reimbursement::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
        
        if (!$isAdmin) {
            $entriesQuery->where('user_id', $userId);
        }
        
        $currentMonthEntries = $entriesQuery->count();

        $pendingQuery = Reimbursement::where('status', 'pending');
        if (!$isAdmin) {
            $pendingQuery->where('user_id', $userId);
        }
        $pendingCount = $pendingQuery->count();

        // Pending reports
        $reportsQuery = Report::whereIn('status', ['draft', 'submitted']);
        if (!$isAdmin) {
            $reportsQuery->where('user_id', $userId);
        }
        $pendingReports = $reportsQuery->count();

        return [
            Stat::make('Total Bulan Ini', 'Rp ' . number_format($currentMonthTotal, 0, ',', '.'))
                ->description($totalTrend >= 0 ? "+{$totalTrend}% dari bulan lalu" : "{$totalTrend}% dari bulan lalu")
                ->descriptionIcon($totalTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($totalTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Entry Bulan Ini', $currentMonthEntries)
                ->description($pendingCount . ' menunggu approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success'),

            Stat::make('Pending Reports', $pendingReports)
                ->description('Laporan belum selesai')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($pendingReports > 0 ? 'warning' : 'success'),
        ];
    }
}
