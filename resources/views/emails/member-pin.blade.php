<x-mail::message>
# Welcome to {{ config('app.name') }}!

Hello **{{ $memberName }}**,

Your gym membership has been successfully created! We're excited to have you join us.

## Your Login Credentials

**Member ID:** {{ $memberId }}

To access your 4-digit PIN and start using the member portal, please click the button below:

<x-mail::button :url="$pinUrl">
View My PIN
</x-mail::button>

**Important:** This link will expire in 48 hours and can only be viewed once, so make sure to save your PIN securely.

## What You Can Do in the Member Portal

- Check in and out of the gym with one tap
- View your membership status and expiration date
- Track your workout history
- View your attendance records

## Getting Started

1. Click the button above to view your PIN
2. Save your Member ID and PIN securely
3. Visit the member portal to login
4. Start tracking your fitness journey!

If you have any questions or need assistance, please don't hesitate to contact our staff at the front desk.

Thank you for choosing {{ config('app.name') }}!

Best regards,<br>
{{ config('app.name') }} Team
</x-mail::message>
