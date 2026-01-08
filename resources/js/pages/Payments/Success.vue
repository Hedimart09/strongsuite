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
        <div
            class="flex h-full flex-1 flex-col items-center justify-center gap-6 p-4 md:p-6"
        >
            <div class="w-full max-w-2xl">
                <!-- Success Icon -->
                <div class="mb-6 flex justify-center">
                    <div
                        class="rounded-full bg-green-100 p-6 dark:bg-green-900/20"
                    >
                        <CheckCircle
                            class="h-16 w-16 text-green-600 dark:text-green-400"
                        />
                    </div>
                </div>

                <!-- Success Message -->
                <div class="mb-8 text-center">
                    <h1 class="mb-2 text-3xl font-bold tracking-tight">
                        Payment Successful!
                    </h1>
                    <p class="text-lg text-muted-foreground">
                        Your payment has been processed successfully
                    </p>
                </div>

                <!-- Payment Details Card -->
                <div
                    class="mb-6 rounded-xl border border-green-200 bg-green-50 p-6 dark:bg-green-950/20"
                >
                    <div class="mb-6 text-center">
                        <p class="mb-2 text-sm text-muted-foreground">
                            Amount Paid
                        </p>
                        <p
                            class="text-4xl font-bold text-green-600 dark:text-green-400"
                        >
                            {{ formatCurrency(payment.amount) }}
                        </p>
                    </div>

                    <div
                        class="space-y-4 border-t border-green-200 pt-6 dark:border-green-900"
                    >
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground"
                                >Transaction ID</span
                            >
                            <span class="font-mono text-sm font-medium">{{
                                payment.transaction_id || 'N/A'
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground"
                                >Payment Method</span
                            >
                            <span class="text-sm font-medium capitalize">
                                {{ payment.payment_method.replace('_', ' ') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground"
                                >Payment Date</span
                            >
                            <span class="text-sm font-medium">{{
                                formatDateTime(payment.payment_date)
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground"
                                >Member</span
                            >
                            <span class="text-sm font-medium">{{
                                payment.member.name
                            }}</span>
                        </div>
                        <div
                            v-if="payment.subscription"
                            class="flex justify-between"
                        >
                            <span class="text-sm text-muted-foreground"
                                >Subscription Plan</span
                            >
                            <span class="text-sm font-medium">{{
                                payment.subscription.membership_plan.name
                            }}</span>
                        </div>
                        <div
                            v-if="payment.invoice"
                            class="flex justify-between"
                        >
                            <span class="text-sm text-muted-foreground"
                                >Invoice</span
                            >
                            <span class="text-sm font-medium">{{
                                payment.invoice.invoice_number
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div
                    class="mb-6 rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 text-lg font-semibold">What's Next?</h2>
                    <ul class="space-y-3 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <CheckCircle
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400"
                            />
                            <span
                                >Your payment has been recorded in our
                                system</span
                            >
                        </li>
                        <li
                            class="flex items-start gap-2"
                            v-if="payment.subscription"
                        >
                            <CheckCircle
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400"
                            />
                            <span
                                >Your subscription has been activated and is now
                                live</span
                            >
                        </li>
                        <li
                            class="flex items-start gap-2"
                            v-if="payment.invoice"
                        >
                            <CheckCircle
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400"
                            />
                            <span>Your invoice has been marked as paid</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <CheckCircle
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400"
                            />
                            <span
                                >A payment receipt has been generated for your
                                records</span
                            >
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="`/payments/${payment.id}`"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        <Eye class="h-4 w-4" />
                        View Payment Details
                    </Link>
                    <Link
                        v-if="payment.invoice"
                        :href="`/invoices/${payment.invoice.id}/pdf`"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-sidebar-border/70 px-4 py-3 text-sm font-medium hover:bg-accent"
                    >
                        <Download class="h-4 w-4" />
                        Download Receipt
                    </Link>
                    <Link
                        href="/dashboard"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-sidebar-border/70 px-4 py-3 text-sm font-medium hover:bg-accent"
                    >
                        <Home class="h-4 w-4" />
                        Go to Dashboard
                    </Link>
                </div>

                <!-- Support Note -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-muted-foreground">
                        Need help? Contact our support team for assistance.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
