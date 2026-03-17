<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Models\Budget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationGroup = 'Finance & Accounting';
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Budget Configuration')
                    ->schema([
                        Forms\Components\Select::make('account_id')
                            ->relationship('account', 'name', fn($query) => $query->where('code', 'like', '5-%')->orWhere('code', 'like', '6-%'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Expense Account'),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('period_month')
                                    ->options([
                                        '01' => 'January', '02' => 'February', '03' => 'March',
                                        '04' => 'April', '05' => 'May', '06' => 'June',
                                        '07' => 'July', '08' => 'August', '09' => 'September',
                                        '10' => 'October', '11' => 'November', '12' => 'December',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('period_year')
                                    ->numeric()
                                    ->default(now()->year)
                                    ->required(),
                            ]),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('branch.name')->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('idr')->sortable(),
                Tables\Columns\TextColumn::make('period_month')->label('Month'),
                Tables\Columns\TextColumn::make('period_year')->label('Year'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
