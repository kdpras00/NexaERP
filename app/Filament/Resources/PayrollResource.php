<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;
    protected static ?string $navigationGroup = 'HR Management';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Payroll Info')
                ->schema([
                    Forms\Components\TextInput::make('number')->disabled()->placeholder('Auto-generated'),
                    Forms\Components\Select::make('employee_id')
                        ->relationship('employee', 'name')
                        ->required()->searchable()->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $employee = \App\Models\Employee::find($state);
                            if ($employee) {
                                $set('basic_salary', $employee->salary);
                                $set('net_salary', $employee->salary);
                            }
                        }),
                    Forms\Components\TextInput::make('month')
                        ->placeholder('MM-YYYY')
                        ->required(),
                    Forms\Components\DatePicker::make('pay_date')->default(now())->required(),
                ])->columns(['md' => 2]),

            Forms\Components\Section::make('Salary Breakdown')
                ->schema([
                    Forms\Components\TextInput::make('basic_salary')
                        ->numeric()->required()->prefix('Rp')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                            $set('net_salary', (float)$state + (float)$get('allowances') - (float)$get('deductions'));
                        }),
                    Forms\Components\TextInput::make('allowances')
                        ->numeric()->default(0)->prefix('Rp')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                            $set('net_salary', (float)$get('basic_salary') + (float)$state - (float)$get('deductions'));
                        }),
                    Forms\Components\TextInput::make('deductions')
                        ->numeric()->default(0)->prefix('Rp')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                            $set('net_salary', (float)$get('basic_salary') + (float)$get('allowances') - (float)$state);
                        }),
                    Forms\Components\TextInput::make('net_salary')
                        ->numeric()->required()->prefix('Rp')->disabled()->dehydrated(),
                ])->columns(['md' => 3]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->searchable(),
                Tables\Columns\TextColumn::make('employee.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('month')->sortable(),
                Tables\Columns\TextColumn::make('net_salary')->money('idr')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
