<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Employee;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static ?string $navigationGroup = 'Human Resources';
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Attendance Record')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TimePicker::make('check_in'),
                        Forms\Components\TimePicker::make('check_out'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'present' => 'Present',
                                'late' => 'Late',
                                'absent' => 'Absent',
                                'permit' => 'Permit',
                                'sick' => 'Sick',
                            ])
                            ->required()
                            ->default('present'),
                        Forms\Components\TextInput::make('location'),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(['md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('employee.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('check_in')->time()->sortable(),
                Tables\Columns\TextColumn::make('check_out')->time()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'present' => 'success',
                            'late' => 'warning',
                            'absent' => 'danger',
                            'permit' => 'info',
                            'sick' => 'warning',
                            default => 'primary',
                        };
                    }),
                Tables\Columns\TextColumn::make('location')->searchable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'name'),
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
