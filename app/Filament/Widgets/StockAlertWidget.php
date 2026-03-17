<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StockAlertWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Low Stock Alerts';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()->whereColumn('stock_quantity', '<=', 'min_stock')
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('SKU'),
                Tables\Columns\TextColumn::make('name')->weight('bold'),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Current Stock')
                    ->numeric()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Safety Stock'),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('restock')
                    ->url(fn (Product $record): string => "/admin/purchase-invoices/create?product_id={$record->id}")
                    ->icon('heroicon-o-shopping-cart')
                    ->button(),
            ]);
    }
}
