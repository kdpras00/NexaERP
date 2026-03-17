<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Expense;
use App\Models\StockMovement;
use App\Models\ProductionOrder;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProjectReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationGroup = 'Financial Reports';
    protected static ?string $navigationLabel = 'Project Profitability';
    protected static ?string $title = 'Project Profitability Report';

    protected static string $view = 'filament.pages.project-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(Project::query())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer'),
                Tables\Columns\TextColumn::make('revenue')
                    ->label('Total Revenue')
                    ->money('idr')
                    ->getStateUsing(fn (Project $record) => SalesInvoice::where('project_id', $record->id)->sum('total')),
                Tables\Columns\TextColumn::make('material_cost')
                    ->label('Material Costs')
                    ->money('idr')
                    ->getStateUsing(function (Project $record) {
                        $purchaseCosts = PurchaseInvoice::where('project_id', $record->id)->sum('total');
                        $internalConsumption = StockMovement::where('project_id', $record->id)
                            ->where('type', 'out')
                            ->select(DB::raw('SUM(quantity * unit_cost) as total'))
                            ->first()->total ?? 0;
                        return $purchaseCosts + $internalConsumption;
                    }),
                Tables\Columns\TextColumn::make('other_expenses')
                    ->label('Other Expenses')
                    ->money('idr')
                    ->getStateUsing(fn (Project $record) => Expense::where('project_id', $record->id)->sum('total')),
                Tables\Columns\TextColumn::make('profit')
                    ->label('Net Profit')
                    ->money('idr')
                    ->getStateUsing(function (Project $record) {
                        $rev = SalesInvoice::where('project_id', $record->id)->sum('total');
                        $purchaseCosts = PurchaseInvoice::where('project_id', $record->id)->sum('total');
                        $internalConsumption = StockMovement::where('project_id', $record->id)
                            ->where('type', 'out')
                            ->select(DB::raw('SUM(quantity * unit_cost) as total'))
                            ->first()->total ?? 0;
                        $exp = Expense::where('project_id', $record->id)->sum('total');
                        return $rev - ($purchaseCosts + $internalConsumption + $exp);
                    })
                    ->color(fn ($state): string => (float)$state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'primary',
                        'on_hold' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'on_hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    ]),
            ]);
    }
}
