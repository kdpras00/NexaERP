<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class CheckLowStock extends Command
{
    protected $signature = 'erp:check-low-stock';
    protected $description = 'Check for products with low stock and notify administrators';

    public function handle()
    {
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'min_stock')->get();

        if ($lowStockProducts->count() > 0) {
            $admin = User::whereHas('roles', function($q){
                $q->where('name', 'Admin');
            })->first();

            if ($admin) {
                Notification::make()
                    ->title('Low Stock Alert')
                    ->warning()
                    ->body("There are {$lowStockProducts->count()} products below safety stock levels.")
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url('/admin/products?tableFilters[low_stock][isActive]=1'),
                    ])
                    ->sendToDatabase($admin);
            }
        }

        $this->info('Low stock check completed.');
    }
}
