<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillOfMaterialResource\Pages;
use App\Models\BillOfMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BillOfMaterialResource extends Resource
{
    protected static ?string $model = BillOfMaterial::class;
    protected static ?string $navigationGroup = 'Manufacturing';
    protected static ?string $navigationIcon = 'heroicon-o-variable';
    protected static ?string $label = 'BoM';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('BoM Details')
                ->schema([
                    Forms\Components\TextInput::make('number')
                        ->disabled()->dehydrated(false)->placeholder('Auto-generated'),
                    Forms\Components\Select::make('product_id')
                        ->label('Finished Good')
                        ->relationship('product', 'name', fn ($query) => $query->where('type', 'finished_good'))
                        ->required()->searchable()->preload(),
                    Forms\Components\TextInput::make('quantity')
                        ->label('Units to Produce')
                        ->numeric()->default(1)->required(),
                    Forms\Components\Toggle::make('is_active')->default(true),
                    Forms\Components\Textarea::make('instructions')->columnSpanFull(),
                ])->columns(['md' => 2]),

            Forms\Components\Section::make('Raw Materials / Components')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Material')
                                ->relationship('product', 'name', fn ($query) => $query->where('type', 'raw_material'))
                                ->required()->searchable()->preload(),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()->required()->minValue(0.01),
                        ])
                        ->columns(['md' => 2])
                        ->defaultItems(1)
                        ->minItems(1)
                        ->grid(['default' => 1]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product.name')->label('Finished Good')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('quantity')->label('Yield Qty')->numeric(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('Components'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillOfMaterials::route('/'),
            'create' => Pages\CreateBillOfMaterial::route('/create'),
            'edit' => Pages\EditBillOfMaterial::route('/{record}/edit'),
        ];
    }
}
