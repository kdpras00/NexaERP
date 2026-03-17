<?php

namespace App\Filament\Widgets;

use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue vs Expenses (6 Months)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $salesData = [];
        $purchaseData = [];
        $profitData = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $sales = SalesInvoice::where('payment_status', 'paid')
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->sum('total');

            $purchases = PurchaseInvoice::where('payment_status', 'paid')
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->sum('total');

            $expenses = Expense::where('status', 'paid')
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->sum('total');

            $totalExpenses = $purchases + $expenses;

            $salesData[] = $sales;
            $purchaseData[] = $totalExpenses;
            $profitData[] = $sales - $totalExpenses;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $salesData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $purchaseData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Net Profit',
                    'data' => $profitData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'type' => 'line',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
