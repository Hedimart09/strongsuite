<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            margin-bottom: 40px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            text-align: right;
            color: #000;
        }
        .invoice-details {
            text-align: right;
            margin-top: 10px;
        }
        .invoice-details div {
            margin: 5px 0;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-table td {
            vertical-align: top;
            width: 50%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th {
            background-color: #f0f0f0;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #000;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            font-size: 14px;
        }
        .subtotal-section {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .subtotal-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .subtotal-row.total {
            border-top: 2px solid #000;
            font-weight: bold;
            font-size: 16px;
            padding-top: 10px;
            margin-top: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-sent {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-draft {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-overdue {
            background-color: #fff3cd;
            color: #856404;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #000;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div class="company-name">Strongsuite Gym</div>
                    <div>Fitness & Wellness Center</div>
                    <div>Accra, Ghana</div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-details">
                        <div><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
                        <div><strong>Issue Date:</strong> {{ $invoice->issue_date->format('d M Y') }}</div>
                        <div><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</div>
                        <div class="status-badge status-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="section-title">Bill To:</div>
                <div><strong>{{ $invoice->member->name }}</strong></div>
                <div>Member ID: {{ $invoice->member->member_id }}</div>
                <div>Email: {{ $invoice->member->email }}</div>
                <div>Phone: {{ $invoice->member->phone }}</div>
                @if($invoice->member->address)
                    <div>{{ $invoice->member->address }}</div>
                @endif
            </td>
            <td style="text-align: right;">
                @if($invoice->subscription)
                    <div class="section-title">Subscription Details:</div>
                    <div><strong>{{ $invoice->subscription->membershipPlan->name }}</strong></div>
                    <div>Start: {{ $invoice->subscription->start_date->format('d M Y') }}</div>
                    <div>End: {{ $invoice->subscription->end_date->format('d M Y') }}</div>
                    <div>Duration: {{ $invoice->subscription->membershipPlan->duration_in_days }} days</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($invoice->subscription)
                        {{ $invoice->subscription->membershipPlan->name }} Membership
                        <div style="font-size: 10px; color: #666; margin-top: 5px;">
                            {{ $invoice->subscription->start_date->format('d M Y') }} - {{ $invoice->subscription->end_date->format('d M Y') }}
                        </div>
                    @else
                        Membership Fee
                    @endif
                </td>
                <td class="text-right">{{ number_format($invoice->subtotal / 100, 2) }} {{ $invoice->currency }}</td>
            </tr>
        </tbody>
    </table>

    <div style="clear: both;"></div>

    <div class="subtotal-section">
        <div class="subtotal-row">
            <div>Subtotal:</div>
            <div>{{ number_format($invoice->subtotal / 100, 2) }} {{ $invoice->currency }}</div>
        </div>
        @if($invoice->tax_amount > 0)
            <div class="subtotal-row">
                <div>Tax (15%):</div>
                <div>{{ number_format($invoice->tax_amount / 100, 2) }} {{ $invoice->currency }}</div>
            </div>
        @endif
        <div class="subtotal-row total">
            <div>Total:</div>
            <div>{{ number_format($invoice->total_amount / 100, 2) }} {{ $invoice->currency }}</div>
        </div>
    </div>

    <div style="clear: both;"></div>

    @if($invoice->notes)
        <div class="notes">
            <div class="section-title">Notes:</div>
            <div>{{ $invoice->notes }}</div>
        </div>
    @endif

    @if($invoice->payments->count() > 0)
        <div style="margin-top: 30px;">
            <div class="section-title">Payment History:</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Transaction ID</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                            <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                            <td class="text-right">{{ number_format($payment->amount / 100, 2) }} {{ $payment->currency }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>For any questions regarding this invoice, please contact us at info@strongsuite.com</p>
    </div>
</body>
</html>
