<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    Calendar,
    CheckCircle,
    Clock,
    CreditCard,
    FileText,
    User,
    XCircle,
} from 'lucide-vue-next';

interface Payment {
    id: number;
    amount: number;
    currency: string;
    payment_method: string;
    payment_gateway: string | null;
    transaction_id: string | null;
    status: string;
    payment_date: string;
    created_at: string;
    metadata: Record<string, any> | null;
    member: {
        id: number;
        name: string;
        member_id: string;
        email: string;
        phone: string;
    };
    subscription: {
        id: number;
        start_date: string;
        end_date: string;
        status: string;
        membership_plan: {
            id: number;
            name: string;
            price: number;
            duration_in_days: number;
        };
    } | null;
    invoice: {
        id: number;
        invoice_number: string;
        total_amount: number;
        status: string;
    } | null;
}

interface Props {
    payment: Payment;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Payments', href: '/payments' },
    {
        title: `Payment #${props.payment.id}`,
        href: `/payments/${props.payment.id}`,
    },
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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed':
            return CheckCircle;
        case 'failed':
            return XCircle;
        case 'pending':
            return Clock;
        default:
            return Clock;
    }
};

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        completed:
            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        pending:
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        refunded:
            'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
    };
    return (
        classes[status] ||
        'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'
    );
};
</script>

<template>
    <Head :title="`Payment #${payment.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Payment Details
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Transaction ID: {{ payment.transaction_id || 'N/A' }}
                    </p>
                </div>
                <Link
                    href="/payments"
                    class="inline-flex items-center gap-2 rounded-lg border border-sidebar-border/70 px-4 py-2 text-sm font-medium hover:bg-accent"
                >
                    Back to Payments
                </Link>
            </div>

            <!-- Status Banner -->
            <div
                class="rounded-xl border p-6"
                :class="{
                    'border-green-200 bg-green-50 dark:bg-green-950/20':
                        payment.status === 'completed',
                    'border-yellow-200 bg-yellow-50 dark:bg-yellow-950/20':
                        payment.status === 'pending',
                    'border-red-200 bg-red-50 dark:bg-red-950/20':
                        payment.status === 'failed',
                }"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <component
                            :is="getStatusIcon(payment.status)"
                            class="h-12 w-12"
                            :class="{
                                'text-green-600 dark:text-green-400':
                                    payment.status === 'completed',
                                'text-yellow-600 dark:text-yellow-400':
                                    payment.status === 'pending',
                                'text-red-600 dark:text-red-400':
                                    payment.status === 'failed',
                            }"
                        />
                        <div>
                            <p class="text-2xl font-bold">
                                {{ formatCurrency(payment.amount) }}
                            </p>
                            <span
                                class="mt-2 inline-block rounded-full px-3 py-1 text-sm font-medium capitalize"
                                :class="getStatusClass(payment.status)"
                            >
                                {{ payment.status }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-muted-foreground">
                            Payment Date
                        </p>
                        <p class="font-medium">
                            {{ formatDateTime(payment.payment_date) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Payment Information -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <CreditCard class="h-5 w-5 text-primary" />
                        <h2 class="text-lg font-semibold">
                            Payment Information
                        </h2>
                    </div>
                    <dl class="space-y-4">
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Payment Method
                            </dt>
                            <dd class="mt-1 text-sm font-semibold capitalize">
                                {{ payment.payment_method.replace('_', ' ') }}
                            </dd>
                        </div>
                        <div v-if="payment.payment_gateway">
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Payment Gateway
                            </dt>
                            <dd class="mt-1 text-sm font-semibold capitalize">
                                {{ payment.payment_gateway }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Transaction ID
                            </dt>
                            <dd class="mt-1 font-mono text-sm">
                                {{ payment.transaction_id || 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Currency
                            </dt>
                            <dd class="mt-1 text-sm font-semibold">
                                {{ payment.currency }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Created At
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{ formatDateTime(payment.created_at) }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Member Information -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <User class="h-5 w-5 text-primary" />
                        <h2 class="text-lg font-semibold">
                            Member Information
                        </h2>
                    </div>
                    <dl class="space-y-4">
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Name
                            </dt>
                            <dd class="mt-1 text-sm font-semibold">
                                <Link
                                    :href="`/members/${payment.member.id}`"
                                    class="text-primary hover:underline"
                                >
                                    {{ payment.member.name }}
                                </Link>
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Member ID
                            </dt>
                            <dd class="mt-1 font-mono text-sm">
                                {{ payment.member.member_id }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Email
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{ payment.member.email }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Phone
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{ payment.member.phone }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Subscription Information -->
                <div
                    v-if="payment.subscription"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <Calendar class="h-5 w-5 text-primary" />
                        <h2 class="text-lg font-semibold">
                            Subscription Information
                        </h2>
                    </div>
                    <dl class="space-y-4">
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Plan
                            </dt>
                            <dd class="mt-1 text-sm font-semibold">
                                {{ payment.subscription.membership_plan.name }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Duration
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{
                                    payment.subscription.membership_plan
                                        .duration_in_days
                                }}
                                days
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Start Date
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{
                                    formatDate(payment.subscription.start_date)
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                End Date
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{ formatDate(payment.subscription.end_date) }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Status
                            </dt>
                            <dd class="mt-1">
                                <span
                                    class="inline-block rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800 capitalize dark:bg-blue-900/20 dark:text-blue-400"
                                >
                                    {{ payment.subscription.status }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Invoice Information -->
                <div
                    v-if="payment.invoice"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <FileText class="h-5 w-5 text-primary" />
                        <h2 class="text-lg font-semibold">
                            Invoice Information
                        </h2>
                    </div>
                    <dl class="space-y-4">
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Invoice Number
                            </dt>
                            <dd class="mt-1 text-sm font-semibold">
                                <Link
                                    :href="`/invoices/${payment.invoice.id}`"
                                    class="text-primary hover:underline"
                                >
                                    {{ payment.invoice.invoice_number }}
                                </Link>
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Total Amount
                            </dt>
                            <dd class="mt-1 text-sm font-semibold">
                                {{
                                    formatCurrency(payment.invoice.total_amount)
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Status
                            </dt>
                            <dd class="mt-1">
                                <span
                                    class="inline-block rounded-full bg-green-100 px-2 py-1 text-xs text-green-800 capitalize dark:bg-green-900/20 dark:text-green-400"
                                >
                                    {{ payment.invoice.status }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Metadata -->
            <div
                v-if="
                    payment.metadata && Object.keys(payment.metadata).length > 0
                "
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h2 class="mb-4 text-lg font-semibold">Transaction Metadata</h2>
                <div class="overflow-x-auto rounded-lg bg-muted/50 p-4">
                    <pre class="text-xs">{{
                        JSON.stringify(payment.metadata, null, 2)
                    }}</pre>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
