<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransferResource\Pages;
use App\Models\StockTransfer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_stock_transfer') ?? true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transfer Info')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('from_warehouse_id')
                            ->relationship('fromWarehouse', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('to_warehouse_id')
                            ->relationship('toWarehouse', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'transit' => 'In Transit',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(['md' => 2]),
                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
                            ])->columns(['md' => 2]),
                    ]),
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('fromWarehouse.name')->sortable(),
                Tables\Columns\TextColumn::make('toWarehouse.name')->sortable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'draft' => 'gray',
                            'transit' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            default => 'primary',
                        };
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'transit' => 'In Transit',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Complete Transfer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (StockTransfer $record) => in_array($record->status, ['draft', 'transit']))
                    ->requiresConfirmation()
                    ->action(function (StockTransfer $record) {
                        foreach ($record->items as $item) {
                            // OUT from source
                            \App\Models\StockMovement::create([
                                'product_id' => $item->product_id,
                                'warehouse_id' => $record->from_warehouse_id,
                                'quantity' => -$item->quantity,
                                'type' => 'out',
                                'reference' => "Transfer OUT to {$record->toWarehouse->name} (#{$record->number})",
                                'date' => now(),
                            ]);

                            // IN to destination
                            \App\Models\StockMovement::create([
                                'product_id' => $item->product_id,
                                'warehouse_id' => $record->to_warehouse_id,
                                'quantity' => $item->quantity,
                                'type' => 'in',
                                'reference' => "Transfer IN from {$record->fromWarehouse->name} (#{$record->number})",
                                'date' => now(),
                            ]);
                            
                            // Stock update for product (overall) doesn't change globally, but we might want to check warehouse specific stock in future.
                        }
                        $record->update(['status' => 'completed']);
                        Notification::make()->title('Transfer Completed')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockTransfers::route('/'),
            'create' => Pages\CreateStockTransfer::route('/create'),
            'edit' => Pages\EditStockTransfer::route('/{record}/edit'),
        ];
    }
}
