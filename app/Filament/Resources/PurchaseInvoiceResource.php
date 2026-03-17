<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseInvoiceResource\Pages;
use App\Models\PurchaseInvoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseInvoiceResource extends Resource
{
    protected static ?string $model = PurchaseInvoice::class;
    protected static ?string $navigationGroup = 'Purchasing';
    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_purchase_invoice') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Header')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->required()->unique(ignoreRecord: true),
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->required()->searchable()->preload(),
                        Forms\Components\DatePicker::make('date')
                            ->required()->default(now()),
                        Forms\Components\DatePicker::make('due_date')
                            ->required()->default(now()->addDays(30)),
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->searchable()->preload()->label('Project / Job'),
                    ])->columns(['md' => 2]),

                Forms\Components\Section::make('Financials')
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()->prefix('Rp')->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get): void { self::calculateTotals($set, $get); }),
                        Forms\Components\Select::make('tax_rate')
                            ->options([0 => 'No Tax (0%)', 11 => 'PPN (11%)'])
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get): void { self::calculateTotals($set, $get); }),
                        Forms\Components\TextInput::make('tax_amount')
                            ->numeric()->prefix('Rp')->disabled()->dehydrated(),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()->prefix('Rp')->disabled()->dehydrated(),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                            ])->default('unpaid')->required(),
                    ])->columns(['md' => 2]),
            ]);
    }

    public static function calculateTotals(callable $set, callable $get): void
    {
        $subtotal = (float) $get('subtotal');
        $taxRate = (float) $get('tax_rate');
        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount;

        $set('tax_amount', $taxAmount);
        $set('total_amount', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->money('idr')->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseInvoices::route('/'),
            'create' => Pages\CreatePurchaseInvoice::route('/create'),
            'edit' => Pages\EditPurchaseInvoice::route('/{record}/edit'),
        ];
    }
}
