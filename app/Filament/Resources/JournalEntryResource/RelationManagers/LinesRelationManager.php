<?php

namespace App\Filament\Resources\JournalEntryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';
    protected static ?string $title = 'Journal Entry Lines';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('account_id')
                ->relationship('account', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->name}")
                ->columnSpan(2),
            Forms\Components\TextInput::make('debit')
                ->required()
                ->numeric()
                ->prefix('Rp')
                ->default(0),
            Forms\Components\TextInput::make('credit')
                ->required()
                ->numeric()
                ->prefix('Rp')
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account.code')
                    ->label('Account Code'),
                Tables\Columns\TextColumn::make('account.name')
                    ->label('Account Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('debit')
                    ->money('idr')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('idr')),
                Tables\Columns\TextColumn::make('credit')
                    ->money('idr')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('idr')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
