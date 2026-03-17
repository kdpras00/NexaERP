<?php

namespace App\Filament\Widgets;

use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\AccountsReceivable;
use App\Models\AccountsPayable;
use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Monthly Sales
        $monthlySales = SalesInvoice::where('payment_status', 'paid')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total');

        // Total Sales YTD
        $totalSalesYTD = SalesInvoice::where('payment_status', 'paid')
            ->whereYear('date', now()->year)
            ->sum('total');

        // Total Purchases YTD
        $totalPurchasesYTD = PurchaseInvoice::where('payment_status', 'paid')
            ->whereYear('date', now()->year)
            ->sum('total');

        // Total Expenses YTD
        $totalExpensesYTD = Expense::where('status', 'paid')
            ->whereYear('date', now()->year)
            ->sum('total');

        // Net Profit (Sales - Purchases - Expenses)
        $netProfit = $totalSalesYTD - $totalPurchasesYTD - $totalExpensesYTD;

        // Inventory Value
        $inventoryValue = DB::table('products')
            ->whereNull('deleted_at')
            ->select(DB::raw('SUM(stock_quantity * cost_price) as total_value'))
            ->value('total_value') ?? 0;

        // Outstanding AR
        $outstandingAR = AccountsReceivable::where('status', 'unpaid')->sum('amount');

        // Outstanding AP
        $outstandingAP = AccountsPayable::where('status', 'unpaid')->sum('amount');

        // Low Stock Products
        $lowStock = Product::whereColumn('stock_quantity', '<=', 'min_stock')
            ->whereNull('deleted_at')
            ->count();

        // Total Customers
        $totalCustomers = Customer::whereNull('deleted_at')->count();

        return [
            Stat::make('Monthly Sales', 'Rp ' . number_format($monthlySales, 0, ',', '.'))
                ->description('Sales this month (paid)')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
            Stat::make('Sales YTD', 'Rp ' . number_format($totalSalesYTD, 0, ',', '.'))
                ->description('Year-to-date sales')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            Stat::make('Purchases YTD', 'Rp ' . number_format($totalPurchasesYTD, 0, ',', '.'))
                ->description('Year-to-date purchases')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Expenses YTD', 'Rp ' . number_format($totalExpensesYTD, 0, ',', '.'))
                ->description('Total company expenses')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),
            Stat::make('Current Net Profit', 'Rp ' . number_format($netProfit, 0, ',', '.'))
                ->description('Operating profit after expenses')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($netProfit >= 0 ? 'success' : 'danger'),
            Stat::make('Inventory Value', 'Rp ' . number_format($inventoryValue, 0, ',', '.'))
                ->description('Total stock value')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            Stat::make('Outstanding AR', 'Rp ' . number_format($outstandingAR, 0, ',', '.'))
                ->description('Unpaid receivables')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color($outstandingAR > 0 ? 'warning' : 'success'),
            Stat::make('Outstanding AP', 'Rp ' . number_format($outstandingAP, 0, ',', '.'))
                ->description('Unpaid payables')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color($outstandingAP > 0 ? 'warning' : 'success'),
            Stat::make('Low Stock Items', $lowStock)
                ->description('Products below minimum')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStock > 0 ? 'danger' : 'success'),
            Stat::make('Total Customers', $totalCustomers)
                ->description('Registered customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
