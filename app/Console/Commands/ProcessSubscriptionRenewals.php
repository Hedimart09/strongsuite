<?php

namespace App\Console\Commands;

use App\Mail\PaymentLinkMail;
use App\Models\Subscription;
use App\Services\InvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessSubscriptionRenewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic subscription renewals and send renewal reminders';

    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing subscription renewals...');

        // Step 1: Send renewal reminders (7 days before expiry)
        $this->sendRenewalReminders();

        // Step 2: Create pending renewals (1 day before expiry)
        $this->createPendingRenewals();

        // Step 3: Mark expired subscriptions
        $this->markExpiredSubscriptions();

        $this->info('Subscription renewal processing completed.');

        return Command::SUCCESS;
    }

    protected function sendRenewalReminders(): void
    {
        $this->info('Checking for subscriptions expiring in 7 days...');

        $expiringIn7Days = Subscription::where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('end_date', '=', now()->addDays(7)->toDateString())
            ->with(['member', 'membershipPlan'])
            ->get();

        $count = 0;

        foreach ($expiringIn7Days as $subscription) {
            // TODO: Create a separate RenewalReminderMail
            // For now, we'll log this
            Log::info('Subscription expiring in 7 days', [
                'subscription_id' => $subscription->id,
                'member_id' => $subscription->member_id,
                'member_email' => $subscription->member->email,
                'end_date' => $subscription->end_date->toDateString(),
            ]);

            $count++;
        }

        $this->info("Sent {$count} renewal reminder(s)");
    }

    protected function createPendingRenewals(): void
    {
        $this->info('Checking for subscriptions expiring tomorrow...');

        $expiringTomorrow = Subscription::where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('end_date', '=', now()->addDay()->toDateString())
            ->with(['member', 'membershipPlan'])
            ->get();

        $count = 0;

        foreach ($expiringTomorrow as $subscription) {
            try {
                DB::transaction(function () use ($subscription) {
                    // Check if renewal already exists
                    $existingRenewal = Subscription::where('member_id', $subscription->member_id)
                        ->where('metadata->renewal_of', $subscription->id)
                        ->whereIn('status', ['pending_payment', 'active'])
                        ->first();

                    if ($existingRenewal) {
                        $this->warn("Renewal already exists for subscription {$subscription->id}");

                        return;
                    }

                    $plan = $subscription->membershipPlan;
                    $newStartDate = $subscription->end_date->copy()->addDay();
                    $newEndDate = $newStartDate->copy()->addDays($plan->duration_in_days);

                    // Create renewal subscription
                    $newSubscription = Subscription::create([
                        'member_id' => $subscription->member_id,
                        'membership_plan_id' => $subscription->membership_plan_id,
                        'start_date' => $newStartDate,
                        'end_date' => $newEndDate,
                        'status' => 'pending_payment',
                        'auto_renew' => true,
                        'metadata' => [
                            'renewal_of' => $subscription->id,
                            'auto_renewal' => true,
                            'created_at' => now()->toIso8601String(),
                        ],
                    ]);

                    // Generate invoice
                    $invoice = $this->invoiceService->generateInvoiceForSubscription($newSubscription);

                    // Initialize payment with Paystack (default gateway)
                    $paymentService = app(\App\Services\PaymentService::class);

                    $paymentData = [
                        'member_id' => $subscription->member_id,
                        'subscription_id' => $newSubscription->id,
                        'amount' => $invoice->total_amount,
                        'currency' => $invoice->currency,
                        'email' => $subscription->member->email,
                        'callback_url' => route('payments.verify', 'paystack'),
                        'metadata' => [
                            'subscription_id' => $newSubscription->id,
                            'invoice_id' => $invoice->id,
                            'member_id' => $subscription->member_id,
                            'auto_renewal' => true,
                        ],
                    ];

                    $result = $paymentService->initializePayment('paystack', $paymentData);

                    if ($result['status'] === 'success') {
                        // Send payment link email
                        Mail::to($subscription->member->email)->send(
                            new PaymentLinkMail(
                                member: $subscription->member,
                                subscription: $newSubscription,
                                paymentLink: $result['authorization_url'],
                                amount: $invoice->total_amount,
                                currency: $invoice->currency
                            )
                        );

                        Log::info('Auto-renewal payment link sent', [
                            'subscription_id' => $newSubscription->id,
                            'member_id' => $subscription->member_id,
                            'email' => $subscription->member->email,
                        ]);
                    } else {
                        Log::error('Failed to initialize auto-renewal payment', [
                            'subscription_id' => $subscription->id,
                            'error' => $result['message'] ?? 'Unknown error',
                        ]);
                    }
                });

                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to process auto-renewal', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Failed to process renewal for subscription {$subscription->id}: {$e->getMessage()}");
            }
        }

        $this->info("Created {$count} pending renewal(s)");
    }

    protected function markExpiredSubscriptions(): void
    {
        $this->info('Marking expired subscriptions...');

        $expiredCount = Subscription::where('status', 'active')
            ->whereDate('end_date', '<', now()->toDateString())
            ->update([
                'status' => 'expired',
                'updated_at' => now(),
            ]);

        $this->info("Marked {$expiredCount} subscription(s) as expired");
    }
}
