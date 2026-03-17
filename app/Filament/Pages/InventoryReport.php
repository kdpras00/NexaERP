<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Product;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class InventoryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.pages.inventory-report';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_report_inventory') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query())
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->searchable(),
                Tables\Columns\TextColumn::make('warehouse.name')->searchable(),
                Tables\Columns\TextColumn::make('stock_quantity')->sortable(),
                Tables\Columns\TextColumn::make('cost_price')->money('idr')->sortable(),
                Tables\Columns\TextColumn::make('selling_price')->money('idr')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
            ]);
    }
}
