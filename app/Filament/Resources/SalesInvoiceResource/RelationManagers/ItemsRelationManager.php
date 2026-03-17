<?php

namespace App\Filament\Resources\SalesInvoiceResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Invoice Items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->relationship('product', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->reactive()
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                    if ($state) {
                        $product = Product::find($state);
                        if ($product) {
                            $set('price', $product->selling_price);
                            $qty = $get('quantity') ?: 1;
                            $set('total', $product->selling_price * $qty);
                        }
                    }
                })
                ->columnSpan(2),
            Forms\Components\TextInput::make('quantity')
                ->required()->numeric()->default(1)->minValue(1)->reactive()
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                    $set('total', ($get('price') ?: 0) * ($state ?: 0));
                }),
            Forms\Components\TextInput::make('price')
                ->required()->numeric()->prefix('Rp')->reactive()
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                    $set('total', ($state ?: 0) * ($get('quantity') ?: 0));
                }),
            Forms\Components\TextInput::make('total')
                ->required()->numeric()->prefix('Rp')->disabled()->dehydrated(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->numeric(),
                Tables\Columns\TextColumn::make('price')->money('idr'),
                Tables\Columns\TextColumn::make('total')
                    ->money('idr')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('idr')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
                Tables\Actions\DeleteAction::make()
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
                ]),
            ]);
    }
}
