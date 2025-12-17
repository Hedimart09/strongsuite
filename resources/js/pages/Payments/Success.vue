<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle, Download, Eye, Home } from 'lucide-vue-next';

interface Payment {
    id: number;
    amount: number;
    currency: string;
    payment_method: string;
    transaction_id: string | null;
    payment_date: string;
    member: {
        id: number;
        name: string;
        member_id: string;
    };
    subscription: {
        id: number;
        membership_plan: {
            name: string;
        };
    } | null;
    invoice: {
        id: number;
        invoice_number: string;
    } | null;
}

interface Props {
    payment: Payment;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Payments', href: '/payments' },
    { title: 'Success', href: '#' },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
    }).format(amount / 100);
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Payment Successful" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col items-center justify-center gap-6 p-4 md:p-6">
            <div class="w-full max-w-2xl">
                <!-- Success Icon -->
                <div class="flex justify-center mb-6">
                    <div class="rounded-full bg-green-100 p-6 dark:bg-green-900/20">
                        <CheckCircle class="h-16 w-16 text-green-600 dark:text-green-400" />
                    </div>
                </div>

                <!-- Success Message -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold tracking-tight mb-2">Payment Successful!</h1>
                    <p class="text-lg text-muted-foreground">
                        Your payment has been processed successfully
                    </p>
                </div>

                <!-- Payment Details Card -->
                <div class="rounded-xl border border-green-200 bg-green-50 dark:bg-green-950/20 p-6 mb-6">
                    <div class="text-center mb-6">
                        <p class="text-sm text-muted-foreground mb-2">Amount Paid</p>
                        <p class="text-4xl font-bold text-green-600 dark:text-green-400">
                            {{ formatCurrency(payment.amount) }}
                        </p>
                    </div>

                    <div class="border-t border-green-200 dark:border-green-900 pt-6 space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Transaction ID</span>
                            <span class="text-sm font-medium font-mono">{{ payment.transaction_id || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Payment Method</span>
                            <span class="text-sm font-medium capitalize">
                                {{ payment.payment_method.replace('_', ' ') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Payment Date</span>
                            <span class="text-sm font-medium">{{ formatDateTime(payment.payment_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Member</span>
                            <span class="text-sm font-medium">{{ payment.member.name }}</span>
                        </div>
                        <div v-if="payment.subscription" class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Subscription Plan</span>
                            <span class="text-sm font-medium">{{ payment.subscription.membership_plan.name }}</span>
                        </div>
                        <div v-if="payment.invoice" class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Invoice</span>
                            <span class="text-sm font-medium">{{ payment.invoice.invoice_number }}</span>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">What's Next?</h2>
                    <ul class="space-y-3 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
                            <span>Your payment has been recorded in our system</span>
                        </li>
                        <li class="flex items-start gap-2" v-if="payment.subscription">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
                            <span>Your subscription has been activated and is now live</span>
                        </li>
                        <li class="flex items-start gap-2" v-if="payment.invoice">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
                            <span>Your invoice has been marked as paid</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
                            <span>A payment receipt has been generated for your records</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <Link
                        :href="`/payments/${payment.id}`"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        <Eye class="h-4 w-4" />
                        View Payment Details
                    </Link>
                    <Link
                        v-if="payment.invoice"
                        :href="`/invoices/${payment.invoice.id}/pdf`"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-sidebar-border/70 px-4 py-3 text-sm font-medium hover:bg-accent"
                    >
                        <Download class="h-4 w-4" />
                        Download Receipt
                    </Link>
                    <Link
                        href="/dashboard"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-sidebar-border/70 px-4 py-3 text-sm font-medium hover:bg-accent"
                    >
                        <Home class="h-4 w-4" />
                        Go to Dashboard
                    </Link>
                </div>

                <!-- Support Note -->
                <div class="text-center mt-8">
                    <p class="text-sm text-muted-foreground">
                        Need help? Contact our support team for assistance.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
