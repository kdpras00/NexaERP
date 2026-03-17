<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionOrderResource\Pages;
use App\Models\ProductionOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ProductionOrderResource extends Resource
{
    protected static ?string $model = ProductionOrder::class;
    protected static ?string $navigationGroup = 'Manufacturing';
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Production Details')
                ->schema([
                    Forms\Components\TextInput::make('number')
                        ->disabled()->dehydrated(false)->placeholder('Auto-generated'),
                    Forms\Components\Select::make('branch_id')
                        ->relationship('branch', 'name')
                        ->required()->searchable()->preload(),
                    Forms\Components\Select::make('project_id')
                        ->relationship('project', 'name')
                        ->searchable()->preload(),
                    Forms\Components\Select::make('bill_of_material_id')
                        ->label('Formula (BoM)')
                        ->relationship('bom', 'number', fn ($query) => $query->where('is_active', true))
                        ->required()->searchable()->preload()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('quantity_to_produce', \App\Models\BillOfMaterial::find($state)?->quantity ?? 1)),
                    Forms\Components\TextInput::make('quantity_to_produce')
                        ->numeric()->required()->minValue(1.0),
                    Forms\Components\DatePicker::make('start_date')->default(now()),
                    Forms\Components\DatePicker::make('end_date'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'confirmed' => 'Confirmed',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                        ])->default('draft')->required(),
                ])->columns(['md' => 2]),
            Forms\Components\Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('bom.product.name')->label('Producing')->sortable(),
                Tables\Columns\TextColumn::make('quantity_to_produce')->label('Target Qty'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'draft' => 'gray',
                            'confirmed' => 'info',
                            'in_progress' => 'warning',
                            'completed' => 'success',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Mark as Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ProductionOrder $record) => $record->status !== 'completed')
                    ->requiresConfirmation()
                    ->action(function (ProductionOrder $record) {
                        $missing = $record->checkStockAvailability();
                        if (!empty($missing)) {
                            $message = "Insufficient stock for: ";
                            foreach ($missing as $m) {
                                $message .= "{$m['product_name']} (Need: {$m['required']}, Have: {$m['available']}) ";
                            }
                            Notification::make()
                                ->title('Production Failed')
                                ->danger()
                                ->body($message)
                                ->send();
                            return;
                        }
                        $record->completeProduction();
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionOrders::route('/'),
            'create' => Pages\CreateProductionOrder::route('/create'),
            'edit' => Pages\EditProductionOrder::route('/{record}/edit'),
        ];
    }
}
