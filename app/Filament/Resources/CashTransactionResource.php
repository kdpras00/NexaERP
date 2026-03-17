<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashTransactionResource\Pages;
use App\Models\CashTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashTransactionResource extends Resource
{
    protected static ?string $model = CashTransaction::class;
    protected static ?string $navigationGroup = 'Finance & Accounting';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_cash_transaction') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'in' => 'Cash In',
                                'out' => 'Cash Out',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('source_or_purpose')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('account_id')
                            ->relationship('account', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                    ])->columns(['md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'in' => 'success',
                            'out' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Cash In',
                        'out' => 'Cash Out',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('source_or_purpose')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reconciliation_status')
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'reconciled' => 'success',
                            'unreconciled' => 'gray',
                            default => 'primary',
                        };
                    }),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('reconciliation_status')
                    ->options([
                        'reconciled' => 'Reconciled',
                        'unreconciled' => 'Unreconciled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('reconcile')
                    ->label('Match Bank')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (CashTransaction $record) => $record->reconciliation_status === 'unreconciled')
                    ->form([
                        Forms\Components\TextInput::make('bank_reference')
                            ->required()
                            ->label('Bank Statement Reference / ID'),
                        Forms\Components\DatePicker::make('matched_at')
                            ->required()
                            ->default(now())
                            ->label('Statement Date'),
                    ])
                    ->action(fn (CashTransaction $record, array $data) => $record->update([
                        'reconciliation_status' => 'reconciled',
                        'matched_at' => $data['matched_at'],
                        'bank_reference' => $data['bank_reference'],
                    ])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashTransactions::route('/'),
            'create' => Pages\CreateCashTransaction::route('/create'),
            'edit' => Pages\EditCashTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
