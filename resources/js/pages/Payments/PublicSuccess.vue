<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, Home, Mail } from 'lucide-vue-next';

interface Payment {
    id: number;
    amount: number;
    currency: string;
    payment_method: string;
    status: string;
    payment_date: string;
    transaction_id: string;
    member: {
        name: string;
        email: string;
        member_id: string;
    };
    subscription?: {
        membership_plan: {
            name: string;
        };
    };
}

interface Props {
    payment: Payment;
}

const props = defineProps<Props>();

const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount / 100);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Payment Successful" />

    <div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 dark:from-gray-900 dark:to-gray-800">
        <div class="container mx-auto flex min-h-screen items-center justify-center px-4 py-16">
            <div class="w-full max-w-2xl">
                <!-- Success Icon -->
                <div class="mb-8 text-center">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                        <CheckCircle2 class="h-12 w-12 text-green-600 dark:text-green-400" />
                    </div>
                    <h1 class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">
                        Payment Successful!
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Thank you for your payment. Your subscription has been activated.
                    </p>
                </div>

                <!-- Payment Details Card -->
                <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-800">
                    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">
                        Payment Details
                    </h2>

                    <div class="space-y-4">
                        <div class="flex justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Amount Paid</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ formatCurrency(payment.amount, payment.currency) }}
                            </span>
                        </div>

                        <div class="flex justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Transaction ID</span>
                            <span class="font-mono text-sm text-gray-900 dark:text-white">
                                {{ payment.transaction_id }}
                            </span>
                        </div>

                        <div class="flex justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Payment Method</span>
                            <span class="text-gray-900 dark:text-white">
                                {{ payment.payment_method }}
                            </span>
                        </div>

                        <div class="flex justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Date & Time</span>
                            <span class="text-gray-900 dark:text-white">
                                {{ formatDate(payment.payment_date) }}
                            </span>
                        </div>

                        <div v-if="payment.subscription" class="flex justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                            <span class="text-gray-600 dark:text-gray-400">Membership Plan</span>
                            <span class="text-gray-900 dark:text-white">
                                {{ payment.subscription.membership_plan.name }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Member</span>
                            <div class="text-right">
                                <div class="text-gray-900 dark:text-white">{{ payment.member.name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ payment.member.member_id }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Receipt Notice -->
                    <div class="mt-6 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                        <div class="flex items-start gap-3">
                            <Mail class="mt-0.5 h-5 w-5 text-blue-600 dark:text-blue-400" />
                            <div>
                                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                    Receipt Sent
                                </p>
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    A payment receipt has been sent to {{ payment.member.email }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="/member/login"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-gray-900 px-6 py-3 text-center font-medium text-white transition-colors hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                        >
                            <Home class="h-5 w-5" />
                            Go to Member Portal
                        </a>
                        <a
                            href="/"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-300 px-6 py-3 text-center font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Back to Home
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <p class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
                    If you have any questions about this payment, please contact our support team.
                </p>
            </div>
        </div>
    </div>
</template>
