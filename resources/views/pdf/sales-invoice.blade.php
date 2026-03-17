<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Invoice #{{ $record->number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 28px; font-weight: bold; color: #3b82f6; }
        .title { font-size: 24px; text-align: right; text-transform: uppercase; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f3f4f6; text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .totals { float: right; width: 300px; }
        .totals table td { border: none; padding: 5px 12px; }
        .grand-total { font-weight: bold; color: #3b82f6; font-size: 18px; }
        .footer { margin-top: 50px; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="border: none; margin-bottom: 0;">
            <tr style="border: none;">
                <td style="border: none; width: 50%;"><div class="logo">NexaERP</div></td>
                <td style="border: none; width: 50%;" class="title">Sales Invoice</td>
            </tr>
        </table>
    </div>

    <table>
        <tr style="border: none;">
            <td style="border: none; width: 50%;">
                <strong>Customer:</strong><br>
                {{ $record->customer->name ?? 'N/A' }}<br>
                {{ $record->customer->address ?? '' }}<br>
                Phone: {{ $record->customer->phone ?? '-' }}
            </td>
            <td style="border: none; width: 50%; text-align: right;">
                <strong>Invoice Number:</strong> {{ $record->number }}<br>
                <strong>Date:</strong> {{ $record->date->format('d M Y') }}<br>
                <strong>Due Date:</strong> {{ $record->due_date->format('d M Y') }}<br>
                <strong>Status:</strong> {{ ucfirst($record->payment_status) }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align: right;">Quantity</th>
                <th style="text-align: right;">Price</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $item)
            <tr>
                <td>{{ $item->product->name ?? 'Unknown' }}</td>
                <td style="text-align: right;">{{ number_format($item->quantity) }}</td>
                <td style="text-align: right;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal</td>
                <td style="text-align: right;">Rp {{ number_format($record->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($record->tax_amount > 0)
            <tr>
                <td>Tax ({{ $record->tax_rate }}%)</td>
                <td style="text-align: right;">Rp {{ number_format($record->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td>Grand Total</td>
                <td style="text-align: right;">Rp {{ number_format($record->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        Generated automatically by NexaERP on {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
