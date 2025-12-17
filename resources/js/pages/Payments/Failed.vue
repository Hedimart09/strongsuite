<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { XCircle, Home, RefreshCw, HelpCircle } from 'lucide-vue-next';

interface Props {
    error?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Payments', href: '/payments' },
    { title: 'Failed', href: '#' },
];
</script>

<template>
    <Head title="Payment Failed" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col items-center justify-center gap-6 p-4 md:p-6">
            <div class="w-full max-w-2xl">
                <!-- Error Icon -->
                <div class="flex justify-center mb-6">
                    <div class="rounded-full bg-red-100 p-6 dark:bg-red-900/20">
                        <XCircle class="h-16 w-16 text-red-600 dark:text-red-400" />
                    </div>
                </div>

                <!-- Error Message -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold tracking-tight mb-2">Payment Failed</h1>
                    <p class="text-lg text-muted-foreground">
                        Unfortunately, we couldn't process your payment
                    </p>
                </div>

                <!-- Error Details Card -->
                <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/20 p-6 mb-6" v-if="error">
                    <h2 class="text-sm font-semibold text-red-900 dark:text-red-200 mb-2">Error Message</h2>
                    <p class="text-sm text-red-800 dark:text-red-300">{{ error }}</p>
                </div>

                <!-- Common Reasons -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">Common Reasons for Payment Failure</h2>
                    <ul class="space-y-3 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2">
                            <div class="h-1.5 w-1.5 rounded-full bg-current mt-2 flex-shrink-0" />
                            <span>Insufficient funds in your account</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="h-1.5 w-1.5 rounded-full bg-current mt-2 flex-shrink-0" />
                            <span>Incorrect card details or expired card</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="h-1.5 w-1.5 rounded-full bg-current mt-2 flex-shrink-0" />
                            <span>Payment was declined by your bank</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="h-1.5 w-1.5 rounded-full bg-current mt-2 flex-shrink-0" />
                            <span>Network or connection issues during payment</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="h-1.5 w-1.5 rounded-full bg-current mt-2 flex-shrink-0" />
                            <span>Transaction timeout or cancellation</span>
                        </li>
                    </ul>
                </div>

                <!-- What to Do -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">What Can You Do?</h2>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/20">
                                <RefreshCw class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="font-medium text-sm">Try Again</p>
                                <p class="text-sm text-muted-foreground">
                                    Double-check your payment details and try the transaction again
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="rounded-full bg-green-100 p-2 dark:bg-green-900/20">
                                <HelpCircle class="h-4 w-4 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="font-medium text-sm">Contact Your Bank</p>
                                <p class="text-sm text-muted-foreground">
                                    Verify with your bank that the payment wasn't blocked or restricted
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="rounded-full bg-purple-100 p-2 dark:bg-purple-900/20">
                                <HelpCircle class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="font-medium text-sm">Use Different Payment Method</p>
                                <p class="text-sm text-muted-foreground">
                                    Try using a different card or payment method
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <Link
                        href="/payments"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        <RefreshCw class="h-4 w-4" />
                        Try Payment Again
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
                        Still having trouble? <Link href="#" class="text-primary hover:underline">Contact our support team</Link> for assistance.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
