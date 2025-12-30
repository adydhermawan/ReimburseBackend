<?php

namespace App\Filament\Widgets;

use App\Models\Reimbursement;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentReimbursements extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reimbursement::query()
                    ->with(['user', 'client', 'category'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('')
                    ->disk('cloudinary')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->limit(20),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'in_report' => 'info',
                        'paid' => 'success',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
