<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesInvoiceResource\Pages;
use App\Filament\Resources\SalesInvoiceResource\RelationManagers;
use App\Models\SalesInvoice;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\SalesInvoiceExporter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesInvoiceResource extends Resource
{
    protected static ?string $model = SalesInvoice::class;
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_sales_invoice') ?? false;
    }
    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Invoice Details')
                ->schema([
                    Forms\Components\TextInput::make('number')
                        ->disabled()->dehydrated(false)->placeholder('Auto-generated'),
                    Forms\Components\Select::make('branch_id')
                        ->relationship('branch', 'name')
                        ->required()->searchable()->preload(),
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->required()->searchable()->preload(),
                    Forms\Components\Select::make('sales_order_id')
                        ->relationship('salesOrder', 'number')
                        ->searchable()->preload()->label('From Sales Order'),
                    Forms\Components\DatePicker::make('date')
                        ->required()->default(now()),
                    Forms\Components\DatePicker::make('due_date')
                        ->required()->default(now()->addDays(30)),
                    Forms\Components\Select::make('payment_status')
                        ->options([
                            'unpaid' => 'Unpaid',
                            'partial' => 'Partial',
                            'paid' => 'Paid',
                        ])->default('unpaid')->required(),
                ])->columns(['md' => 2]),
            Forms\Components\Section::make('Financials')
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->numeric()->prefix('Rp')->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            self::calculateTotals($set, $get);
                        }),
                    Forms\Components\Select::make('tax_rate')
                        ->options([0 => 'No Tax (0%)', 11 => 'PPN (11%)'])
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            self::calculateTotals($set, $get);
                        }),
                    Forms\Components\TextInput::make('tax_amount')
                        ->numeric()->prefix('Rp')->disabled()->dehydrated(),
                    Forms\Components\TextInput::make('total')
                        ->numeric()->prefix('Rp')->disabled()->dehydrated(),
                ])->columns(['md' => 2]),
        ]);
    }

    public static function calculateTotals(callable $set, callable $get): void
    {
        $subtotal = (float) $get('subtotal');
        $taxRate = (float) $get('tax_rate');
        $taxAmount = ($subtotal * $taxRate) / 100;
        $total = $subtotal + $taxAmount;

        $set('tax_amount', $taxAmount);
        $set('total', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'paid' => 'success',
                            'partial' => 'warning',
                            'unpaid' => 'danger',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('total')->money('idr')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                ExportAction::make()->exporter(SalesInvoiceExporter::class),
                Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-on-square')
                    ->color('gray')
                    ->action(function (SalesInvoice $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sales-invoice', ['record' => $record])->output();
                        }, "INV-{$record->number}.pdf");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exporter(SalesInvoiceExporter::class),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesInvoices::route('/'),
            'create' => Pages\CreateSalesInvoice::route('/create'),
            'edit' => Pages\EditSalesInvoice::route('/{record}/edit'),
        ];
    }
}
