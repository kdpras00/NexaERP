<?php

namespace App\Filament\Widgets;

use App\Models\CashTransaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactions extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CashTransaction::query()->latest('date')->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Cash In',
                        'out' => 'Cash Out',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('account.name')
                    ->label('Account'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('idr'),
                Tables\Columns\TextColumn::make('date')
                    ->date(),
            ])
            ->paginated(false);
    }
}
