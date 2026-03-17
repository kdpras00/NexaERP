<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FixedAssetResource\Pages;
use App\Models\FixedAsset;
use App\Services\AssetService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class FixedAssetResource extends Resource
{
    protected static ?string $model = FixedAsset::class;
    protected static ?string $navigationGroup = 'Asset Management';
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Asset Details')
                    ->schema([
                        Forms\Components\TextInput::make('asset_code')->required()->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\Select::make('category')
                            ->options([
                                'furniture' => 'Furniture & Fixture',
                                'equipment' => 'Office Equipment',
                                'vehicle' => 'Motor Vehicle',
                                'building' => 'Building',
                                'machinery' => 'Machinery',
                            ])->required(),
                        Forms\Components\DatePicker::make('purchase_date')->required()->default(now()),
                        Forms\Components\TextInput::make('purchase_cost')
                            ->numeric()->prefix('Rp')->required(),
                        Forms\Components\TextInput::make('salvage_value')
                            ->numeric()->prefix('Rp')->default(0),
                        Forms\Components\TextInput::make('useful_life_years')
                            ->numeric()->label('Useful Life (Years)')->required()->minValue(1),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->searchable()->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'sold' => 'Sold',
                                'disposed' => 'Disposed',
                            ])->required()->default('active'),
                    ])->columns(['md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('runDepreciation')
                    ->label('Run Monthly Depreciation')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function () {
                        $count = AssetService::runMonthlyDepreciation();
                        Notification::make()
                            ->title('Depreciation Processed')
                            ->body("Successfully processed depreciation for {$count} assets.")
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('asset_code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('purchase_cost')->money('idr'),
                Tables\Columns\TextColumn::make('book_value')
                    ->label('Net Book Value')
                    ->money('idr')
                    ->getStateUsing(fn (FixedAsset $record) => $record->purchase_cost - $record->accumulated_depreciation),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'sold' => 'info',
                        'disposed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category'),
                Tables\Filters\SelectFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFixedAssets::route('/'),
            'create' => Pages\CreateFixedAsset::route('/create'),
            'edit' => Pages\EditFixedAsset::route('/{record}/edit'),
        ];
    }
}
