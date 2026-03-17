<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationGroup = 'Finance & Accounting';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Expense Information')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->disabled()->dehydrated(false)->placeholder('Auto-generated'),
                        Forms\Components\DatePicker::make('date')->required()->default(now()),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->required()->searchable()->preload(),
                        Forms\Components\Select::make('expense_category_id')
                            ->relationship('category', 'name')
                            ->required()->searchable()->preload(),
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->searchable()->preload()->label('Project (Optional)'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Payment & Totals')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()->prefix('Rp')->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get): void { self::updateTotal($set, $get); }),
                        Forms\Components\TextInput::make('tax_amount')
                            ->numeric()->prefix('Rp')->default(0)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get): void { self::updateTotal($set, $get); }),
                        Forms\Components\TextInput::make('total')
                            ->numeric()->prefix('Rp')->disabled()->dehydrated(),
                        Forms\Components\Select::make('payment_method')
                            ->options(['cash' => 'Cash', 'bank' => 'Bank Transfer'])
                            ->required()->default('cash'),
                        Forms\Components\Select::make('status')
                            ->options(['paid' => 'Paid', 'pending' => 'Pending'])
                            ->required()->default('paid'),
                    ])->columns(['md' => 2]),

                Forms\Components\Section::make('Additional Data')
                    ->schema([
                        Forms\Components\FileUpload::make('receipt_path')
                            ->label('Receipt / Invoice File')
                            ->disk('public')->directory('receipts'),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ])->collapsed(),
            ]);
    }

    public static function updateTotal(callable $set, callable $get): void
    {
        $amount = (float) ($get('amount') ?? 0);
        $tax = (float) ($get('tax_amount') ?? 0);
        $set('total', $amount + $tax);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->sortable(),
                Tables\Columns\TextColumn::make('project.name')->label('Project')->placeholder('-'),
                Tables\Columns\TextColumn::make('total')->money('idr')->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
