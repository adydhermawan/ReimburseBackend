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

        // Current month stats
        $currentMonthTotal = Reimbursement::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $lastMonthTotal = Reimbursement::whereMonth('transaction_date', $lastMonth->month)
            ->whereYear('transaction_date', $lastMonth->year)
            ->sum('amount');

        $totalTrend = $lastMonthTotal > 0
            ? round((($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1)
            : 0;

        // Entry counts
        $currentMonthEntries = Reimbursement::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->count();

        $pendingCount = Reimbursement::where('status', 'pending')->count();

        // Pending reports
        $pendingReports = Report::whereIn('status', ['draft', 'submitted'])->count();

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
