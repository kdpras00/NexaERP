<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationGroup = 'System Settings';
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_activity_log') ?? false;
    }
    protected static ?string $navigationLabel = 'Activity Logs';
    protected static ?string $modelLabel = 'Activity Log';
    protected static ?string $pluralModelLabel = 'Activity Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('log_name'),
                Forms\Components\TextInput::make('description'),
                Forms\Components\TextInput::make('subject_type'),
                Forms\Components\TextInput::make('subject_id'),
                Forms\Components\TextInput::make('causer_type'),
                Forms\Components\TextInput::make('causer_id'),
                Forms\Components\KeyValue::make('properties'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('log_name')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : null),
                Tables\Columns\TextColumn::make('causer_type')
                    ->label('Causer')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : null),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs::route('/'),
        ];
    }
}
