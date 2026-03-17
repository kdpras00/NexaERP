<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QualityControlResource\Pages;
use App\Models\QualityControl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QualityControlResource extends Resource
{
    protected static ?string $model = QualityControl::class;
    protected static ?string $navigationGroup = 'Manufacturing';
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('QC Details')
                ->schema([
                    Forms\Components\Select::make('production_order_id')
                        ->relationship('productionOrder', 'number')
                        ->required()->searchable()->preload(),
                    Forms\Components\Select::make('inspector_id')
                        ->relationship('inspector', 'name')
                        ->required()->searchable()->preload()->default(auth()->id()),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pass' => 'Pass',
                            'fail' => 'Fail',
                            'needs_rework' => 'Needs Rework',
                        ])->required(),
                    Forms\Components\DateTimePicker::make('checked_at')->default(now())->required(),
                    Forms\Components\TextInput::make('passed_quantity')->numeric()->required(),
                    Forms\Components\TextInput::make('failed_quantity')->numeric()->required()->default(0),
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('productionOrder.number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('inspector.name')->label('Inspector'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pass' => 'success',
                        'fail' => 'danger',
                        'needs_rework' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('passed_quantity')->numeric(),
                Tables\Columns\TextColumn::make('failed_quantity')->numeric(),
                Tables\Columns\TextColumn::make('checked_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQualityControls::route('/'),
            'create' => Pages\CreateQualityControl::route('/create'),
            'edit' => Pages\EditQualityControl::route('/{record}/edit'),
        ];
    }
}
