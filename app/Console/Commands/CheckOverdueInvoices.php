<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\User;
use Filament\Notifications\Notification;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'erp:check-overdue';
    protected $description = 'Check for overdue sales and purchase invoices and notify admins';

    public function handle()
    {
        $overdueSales = SalesInvoice::where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();

        $overduePurchases = PurchaseInvoice::where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();

        if ($overdueSales->count() > 0 || $overduePurchases->count() > 0) {
            $admins = User::role('Admin')->get();

            foreach ($admins as $admin) {
                if ($overdueSales->count() > 0) {
                    Notification::make()
                        ->title('Overdue Sales Invoices')
                        ->danger()
                        ->body("There are {$overdueSales->count()} sales invoices past their due date.")
                        ->sendToDatabase($admin);
                }

                if ($overduePurchases->count() > 0) {
                    Notification::make()
                        ->title('Overdue Purchase Invoices')
                        ->warning()
                        ->body("There are {$overduePurchases->count()} purchase invoices past their due date.")
                        ->sendToDatabase($admin);
                }
            }
        }

        $this->info('Overdue check completed.');
    }
}
