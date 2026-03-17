<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_product') ?? false;
    }
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'inventory' => 'Inventory Item',
                                'raw_material' => 'Raw Material',
                                'finished_good' => 'Finished Good',
                                'service' => 'Service',
                            ])
                            ->default('inventory')
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                            ]),
                        Forms\Components\Select::make('unit_id')
                            ->relationship('unit', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(['md' => 2]),

                Forms\Components\Section::make('Pricing & Inventory')
                    ->schema([
                        Forms\Components\TextInput::make('cost_price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\TextInput::make('selling_price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('min_stock')
                            ->label('Minimum Stock (Alert Threshold)')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\Select::make('warehouse_id')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(['md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'inventory' => 'gray',
                            'raw_material' => 'warning',
                            'finished_good' => 'success',
                            'service' => 'info',
                            default => 'primary',
                        };
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()->searchable()
                    ->badge()->color('info'),
                Tables\Columns\TextColumn::make('unit.abbreviation')
                    ->label('Unit')->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()->sortable()
                    ->color(function ($record): string {
                        /** @var Product $record */
                        return $record->isLowStock() ? 'danger' : 'success';
                    })
                    ->icon(fn ($record) => $record->isLowStock() ? 'heroicon-o-exclamation-triangle' : null),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('idr')->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('warehouse')
                    ->relationship('warehouse', 'name'),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock Only')
                    ->query(fn (Builder $query) => $query->whereColumn('stock_quantity', '<=', 'min_stock')),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
