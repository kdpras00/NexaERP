<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoodsReceiptResource\Pages;
use App\Filament\Resources\GoodsReceiptResource\RelationManagers;
use App\Models\GoodsReceipt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GoodsReceiptResource extends Resource
{
    protected static ?string $model = GoodsReceipt::class;

    protected static ?string $navigationGroup = 'Purchasing';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_goods_receipt') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Goods Receipt Details')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('purchase_order_id')
                            ->relationship('purchaseOrder', 'number')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('warehouse_id')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'processed' => 'Processed',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchaseOrder.number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'processed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('process')
                    ->label('Process Receipt')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (GoodsReceipt $record) => $record->status === 'draft')
                    ->action(function (GoodsReceipt $record) {
                        $record->update(['status' => 'processed']);
                        
                        // Increment stock and create movements
                        foreach ($record->items as $item) {
                            $product = \App\Models\Product::find($item->product_id);
                            if ($product) {
                                $product->increment('stock_quantity', $item->quantity);
                                
                                // Create Stock Movement
                                \App\Models\StockMovement::create([
                                    'product_id' => $item->product_id,
                                    'warehouse_id' => $record->warehouse_id,
                                    'quantity' => $item->quantity,
                                    'type' => 'in',
                                    'reference' => "Goods Receipt #{$record->number}",
                                    'date' => $record->date,
                                ]);
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Receipt Processed')
                            ->body("Goods Received #{$record->number} added to inventory.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoodsReceipts::route('/'),
            'create' => Pages\CreateGoodsReceipt::route('/create'),
            'edit' => Pages\EditGoodsReceipt::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
