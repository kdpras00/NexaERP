<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountsPayableResource\Pages;
use App\Filament\Resources\AccountsPayableResource\RelationManagers;
use App\Models\AccountsPayable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountsPayableResource extends Resource
{
    protected static ?string $model = AccountsPayable::class;

    protected static ?string $navigationGroup = 'Finance & Accounting';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_accounts_payable') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payable Details')
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('purchase_invoice_id')
                            ->relationship('purchaseInvoice', 'number')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\DatePicker::make('due_date')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                            ])
                            ->default('unpaid')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchaseInvoice.number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountsPayables::route('/'),
            'create' => Pages\CreateAccountsPayable::route('/create'),
            'edit' => Pages\EditAccountsPayable::route('/{record}/edit'),
        ];
    }
}
