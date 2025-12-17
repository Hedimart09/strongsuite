<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; padding: 40px; }
        .header { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
        .logo { font-size: 24px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background-color: #f3f4f6; padding: 10px; text-align: left; font-size: 11px; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        .totals { margin-left: auto; width: 300px; margin-top: 20px; }
        .total-row { padding: 8px; display: table; width: 100%; }
        .grand-total { background-color: #f3f4f6; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">{{ config('app.name') }}</div>
        <div style="margin-top: 10px;"><strong>INVOICE {{ $invoice->invoice_number }}</strong></div>
        <div>Status: {{ strtoupper($invoice->status) }}</div>
    </div>

    <table style="margin-bottom: 20px; border: none;">
        <tr>
            <td style="border: none;"><strong>From:</strong><br>{{ config('app.name') }}</td>
            <td style="border: none;"><strong>Bill To:</strong><br>{{ $invoice->member->name }}<br>{{ $invoice->member->email }}</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Issue Date:</strong> {{ $invoice->issue_date->format('M d, Y') }}</td>
            <td style="border: none;"><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->subscription)
            <tr>
                <td>{{ $invoice->subscription->membershipPlan->name }}</td>
                <td style="text-align: right;">GHS {{ number_format($invoice->subtotal / 100, 2) }}</td>
            </tr>
            @elseif($invoice->metadata && isset($invoice->metadata['items']))
                @foreach($invoice->metadata['items'] as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td style="text-align: right;">GHS {{ number_format($item['amount'] / 100, 2) }}</td>
                </tr>
                @endforeach
            @else
            <tr>
                <td>Service/Product</td>
                <td style="text-align: right;">GHS {{ number_format($invoice->subtotal / 100, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <span style="float: left;">Subtotal</span>
            <span style="float: right;">GHS {{ number_format($invoice->subtotal / 100, 2) }}</span>
        </div>
        @if($invoice->tax_amount > 0)
        <div class="total-row">
            <span style="float: left;">Tax</span>
            <span style="float: right;">GHS {{ number_format($invoice->tax_amount / 100, 2) }}</span>
        </div>
        @endif
        <div class="total-row grand-total">
            <span style="float: left;">Total</span>
            <span style="float: right;">GHS {{ number_format($invoice->total_amount / 100, 2) }}</span>
        </div>
    </div>

    @if($invoice->notes)
    <div style="margin-top: 30px; padding: 15px; background-color: #f9fafb;">
        <strong>Notes:</strong><br>{{ $invoice->notes }}
    </div>
    @endif

    <div style="margin-top: 50px; text-align: center; color: #999; font-size: 10px;">
        <p>Thank you for your business!</p>
    </div>
</body>
</html>
