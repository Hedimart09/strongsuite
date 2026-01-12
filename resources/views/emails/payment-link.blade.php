<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Payment Link</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px 20px;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            background-color: #4F46E5;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        .details {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
    </div>

    <div class="content">
        <h2>Hello {{ $member->name }},</h2>

        <p>Your payment link for subscription renewal has been generated successfully.</p>

        <div class="details">
            <p><strong>Subscription Plan:</strong> {{ $subscription->membershipPlan->name }}</p>
            <p><strong>Amount:</strong> {{ number_format($amount / 100, 2) }} {{ $currency }}</p>
            <p><strong>Member ID:</strong> {{ $member->member_id }}</p>
        </div>

        <p>Click the button below to complete your payment securely:</p>

        <div style="text-align: center;">
            <a href="{{ $paymentLink }}" class="button">Pay Now</a>
        </div>

        <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
            If the button doesn't work, you can copy and paste this link into your browser:<br>
            <a href="{{ $paymentLink }}">{{ $paymentLink }}</a>
        </p>

        <p>This payment link is secure and will expire after use.</p>
    </div>

    <div class="footer">
        <p>This is an automated message from {{ config('app.name') }}.</p>
        <p>Please do not reply to this email.</p>
    </div>
</body>
</html>
