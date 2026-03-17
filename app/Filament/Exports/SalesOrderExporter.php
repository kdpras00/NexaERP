<?php

namespace App\Filament\Exports;

use App\Models\SalesOrder;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SalesOrderExporter extends Exporter
{
    protected static ?string $model = SalesOrder::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('number')->label('Order Number'),
            ExportColumn::make('date')->label('Date'),
            ExportColumn::make('customer.name')->label('Customer'),
            ExportColumn::make('branch.name')->label('Branch'),
            ExportColumn::make('project.name')->label('Project'),
            ExportColumn::make('subtotal')->label('Subtotal'),
            ExportColumn::make('tax_amount')->label('Tax'),
            ExportColumn::make('total')->label('Grand Total'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('approved_at')->label('Approved Date'),
            ExportColumn::make('created_at')->label('Created At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sales order export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
