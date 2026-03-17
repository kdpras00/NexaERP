<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOpnameResource\Pages;
use App\Filament\Resources\StockOpnameResource\RelationManagers;
use App\Models\StockOpname;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_stock_opname') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        self::updateSystemStock($set, $get);
                    }),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        self::updateSystemStock($set, $get);
                    }),
                Forms\Components\TextInput::make('system_stock')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->readOnly(),
                Forms\Components\TextInput::make('physical_stock')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        $set('difference', (int)$state - (int)$get('system_stock'));
                    }),
                Forms\Components\TextInput::make('difference')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->readOnly(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    protected static function updateSystemStock(callable $set, callable $get): void
    {
        $productId = $get('product_id');
        if ($productId) {
            $product = \App\Models\Product::find($productId);
            $stock = $product ? $product->stock_quantity : 0;
            $set('system_stock', $stock);
            $set('difference', (int)$get('physical_stock') - $stock);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('warehouse_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('system_stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('physical_stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('difference')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListStockOpnames::route('/'),
            'create' => Pages\CreateStockOpname::route('/create'),
            'edit' => Pages\EditStockOpname::route('/{record}/edit'),
        ];
    }
}
