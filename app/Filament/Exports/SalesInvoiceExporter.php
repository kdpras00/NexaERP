<?php

namespace App\Filament\Exports;

use App\Models\SalesInvoice;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SalesInvoiceExporter extends Exporter
{
    protected static ?string $model = SalesInvoice::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('number')->label('Invoice Number'),
            ExportColumn::make('date')->label('Date'),
            ExportColumn::make('due_date')->label('Due Date'),
            ExportColumn::make('customer.name')->label('Customer'),
            ExportColumn::make('branch.name')->label('Branch'),
            ExportColumn::make('subtotal')->label('Subtotal'),
            ExportColumn::make('tax_amount')->label('Tax'),
            ExportColumn::make('total')->label('Total'),
            ExportColumn::make('payment_status')->label('Payment Status'),
            ExportColumn::make('created_at')->label('Created At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sales invoice export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
