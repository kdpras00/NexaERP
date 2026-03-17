<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class InventoryAlerts extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = '`Low Stock Alerts';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->whereColumn('stock_quantity', '<=', 'min_stock')
                    ->orderBy('stock_quantity', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Warehouse'),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Current Stock')
                    ->numeric()
                    ->color('danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Min. Stock')
                    ->numeric(),
                Tables\Columns\TextColumn::make('unit.abbreviation')
                    ->label('Unit'),
            ])
            ->emptyStateHeading('All stock levels are healthy!')
            ->emptyStateDescription('No products are below their minimum stock level.')
            ->paginated(false);
    }
}
