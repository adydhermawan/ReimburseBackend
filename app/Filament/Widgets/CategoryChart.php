<?php

namespace App\Filament\Widgets;

use App\Models\Reimbursement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Pengeluaran per Kategori (Bulan Ini)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $query = Reimbursement::query()
            ->join('categories', 'reimbursements.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(reimbursements.amount) as total'))
            ->whereMonth('reimbursements.transaction_date', now()->month)
            ->whereYear('reimbursements.transaction_date', now()->year);
        
        // Non-admin users only see their own data
        if (!auth()->user()->isAdmin()) {
            $query->where('reimbursements.user_id', auth()->id());
        }
        
        $data = $query->groupBy('categories.id', 'categories.name')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total (Rp)',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#00bcd4',
                        '#26c6da',
                        '#4dd0e1',
                        '#80deea',
                        '#b2ebf2',
                        '#e0f7fa',
                        '#0097a7',
                        '#00838f',
                    ],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
    }
}
