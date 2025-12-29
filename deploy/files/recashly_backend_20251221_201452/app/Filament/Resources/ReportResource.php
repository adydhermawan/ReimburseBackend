<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\Models\Reimbursement;
use App\Services\PdfReportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->options(\App\Models\User::all()->pluck('name', 'id')->toArray())
                            ->required()
                            ->disabled(fn (?Report $record) => $record !== null),

                        Forms\Components\DatePicker::make('period_start')
                            ->label('Period Start')
                            ->required()
                            ->default(now()->startOfMonth()),

                        Forms\Components\DatePicker::make('period_end')
                            ->label('Period End')
                            ->required()
                            ->default(now()->endOfMonth())
                            ->afterOrEqual('period_start'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'generated' => 'Generated',
                                'submitted' => 'Submitted',
                                'paid' => 'Paid',
                            ])
                            ->required()
                            ->default('draft'),

                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'paid'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Summary')
                    ->schema([
                        Forms\Components\Placeholder::make('total_amount_display')
                            ->label('Total Amount')
                            ->content(fn (?Report $record) => $record
                                ? 'Rp ' . number_format($record->total_amount, 0, ',', '.')
                                : '-'),

                        Forms\Components\Placeholder::make('entry_count_display')
                            ->label('Total Entries')
                            ->content(fn (?Report $record) => $record
                                ? $record->entry_count . ' entries'
                                : '-'),
                    ])
                    ->columns(2)
                    ->visible(fn (?Report $record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('period_label')
                    ->label('Period')
                    ->sortable(['period_start']),

                Tables\Columns\TextColumn::make('entry_count')
                    ->label('Entries')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'generated' => 'info',
                        'submitted' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('pdf_path')
                    ->label('PDF')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-arrow-down')
                    ->falseIcon('heroicon-o-x-mark'),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Paid On')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'generated' => 'Generated',
                        'submitted' => 'Submitted',
                        'paid' => 'Paid',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('generatePdf')
                    ->label('Generate PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->visible(fn (Report $record) => in_array($record->status, ['draft', 'generated']))
                    ->action(function (Report $record) {
                        try {
                            $service = new PdfReportService();
                            $service->generate($record);

                            Notification::make()
                                ->title('PDF Generated Successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to generate PDF')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (Report $record) => $record->pdf_path !== null)
                    ->url(fn (Report $record) => $record->pdf_url)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('markAsPaid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Report $record) => $record->status === 'submitted')
                    ->form([
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->default(now()),
                    ])
                    ->action(function (Report $record, array $data) {
                        $record->update([
                            'status' => 'paid',
                            'payment_date' => $data['payment_date'],
                        ]);

                        // Update all reimbursements in this report
                        $record->reimbursements()->update(['status' => 'paid']);

                        Notification::make()
                            ->title('Report marked as paid')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Report Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('User'),
                        Infolists\Components\TextEntry::make('period_label')
                            ->label('Period'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'generated' => 'info',
                                'submitted' => 'warning',
                                'paid' => 'success',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Total Amount')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('entry_count')
                            ->label('Total Entries')
                            ->suffix(' entries'),
                        Infolists\Components\TextEntry::make('payment_date')
                            ->label('Payment Date')
                            ->date('d F Y')
                            ->placeholder('Not paid yet'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Included Reimbursements')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('reimbursements')
                            ->schema([
                                Infolists\Components\TextEntry::make('transaction_date')
                                    ->label('Date')
                                    ->date('d/m/Y'),
                                Infolists\Components\TextEntry::make('client.name')
                                    ->label('Client'),
                                Infolists\Components\TextEntry::make('category.name')
                                    ->label('Category'),
                                Infolists\Components\TextEntry::make('amount')
                                    ->label('Amount')
                                    ->money('IDR'),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'submitted')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
