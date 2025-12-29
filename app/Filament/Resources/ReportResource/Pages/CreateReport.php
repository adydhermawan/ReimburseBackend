<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\Reimbursement;
use Filament\Resources\Pages\CreateRecord;

class CreateReport extends CreateRecord
{
    protected static string $resource = ReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Get pending and approved reimbursements for the user within the period
        $reimbursements = Reimbursement::where('user_id', $data['user_id'])
            ->whereNull('report_id')
            ->whereIn('status', ['pending', 'approved'])
            ->whereBetween('transaction_date', [$data['period_start'], $data['period_end']])
            ->get();

        // Calculate totals
        $data['total_amount'] = $reimbursements->sum('amount');
        $data['entry_count'] = $reimbursements->count();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Link pending/approved reimbursements to this report
        $reimbursements = Reimbursement::where('user_id', $this->record->user_id)
            ->whereNull('report_id')
            ->whereIn('status', ['pending', 'approved'])
            ->whereBetween('transaction_date', [$this->record->period_start, $this->record->period_end])
            ->get();

        foreach ($reimbursements as $reimbursement) {
            $reimbursement->update([
                'report_id' => $this->record->id,
                'status' => 'in_report',
            ]);
        }

        // Refresh totals
        $this->record->update([
            'total_amount' => $reimbursements->sum('amount'),
            'entry_count' => $reimbursements->count(),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
