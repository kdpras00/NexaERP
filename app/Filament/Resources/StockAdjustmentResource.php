<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Models\StockAdjustment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-vertical';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_stock_adjustment') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stock Adjustment')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('warehouse_id')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('adjustment_type')
                            ->options([
                                'increase' => 'Increase (+)',
                                'decrease' => 'Decrease (-)',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1.0),
                        Forms\Components\Textarea::make('reason')
                            ->columnSpanFull(),
                    ])->columns(['md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('warehouse.name')->sortable(),
                Tables\Columns\TextColumn::make('adjustment_type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'increase' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockAdjustments::route('/'),
            'create' => Pages\CreateStockAdjustment::route('/create'),
            'edit' => Pages\EditStockAdjustment::route('/{record}/edit'),
        ];
    }
}
