<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;

class InvoiceService
{
    public function generateInvoiceForSubscription(Subscription $subscription): Invoice
    {
        $subscription->load('membershipPlan', 'member');
        $plan = $subscription->membershipPlan;

        $subtotal = $plan->price;
        $taxRate = 0; // No tax for now, can be configured later
        $taxAmount = (int) ($subtotal * $taxRate);
        $totalAmount = $subtotal + $taxAmount;

        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'member_id' => $subscription->member_id,
            'subscription_id' => $subscription->id,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => $plan->currency,
            'issue_date' => now(),
            'due_date' => now(), // Immediate payment required for subscriptions
            'status' => 'sent', // Ready for payment
            'metadata' => [
                'subscription_start_date' => $subscription->start_date->toDateString(),
                'subscription_end_date' => $subscription->end_date->toDateString(),
                'plan_name' => $plan->name,
                'plan_duration' => $plan->duration_in_days,
            ],
        ]);

        return $invoice;
    }

    public function linkPaymentToInvoice(Payment $payment, Invoice $invoice): void
    {
        $payment->update([
            'invoice_id' => $invoice->id,
        ]);

        $this->updateInvoiceStatusFromPayments($invoice);
    }

    public function updateInvoiceStatusFromPayments(Invoice $invoice): void
    {
        $invoice->load('payments');

        $totalPaid = $invoice->payments()
            ->where('status', 'completed')
            ->sum('amount');

        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }

    protected function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-'.str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Invoice::where('invoice_number', $number)->exists());

        return $number;
    }
}
