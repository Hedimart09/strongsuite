<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    CheckCircle,
    CreditCard,
    DollarSign,
    FileText,
    TrendingUp,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Metrics {
    total_revenue: number;
    today_revenue: number;
    month_revenue: number;
    last_month_revenue: number;
    average_transaction: number;
    total_payments: number;
    outstanding_amount: number;
    overdue_count: number;
    overdue_amount: number;
    due_soon_count: number;
    completed_payments: number;
    failed_payments: number;
    pending_payments: number;
    paying_members: number;
    active_subscriptions_value: number;
    success_rate: number;
    month_growth: number;
    avg_revenue_per_member: number;
}

interface Payment {
    id: number;
    amount: number;
    currency: string;
    payment_method: string;
    payment_gateway: string | null;
    transaction_id: string | null;
    status: string;
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
}

interface Invoice {
    id: number;
    invoice_number: string;
    total_amount: number;
    currency: string;
    status: string;
    due_date: string;
    days_overdue: number;
    member: {
        id: number;
        name: string;
        member_id: string;
    };
}

interface TopPayingMember {
    member_id: number;
    total_paid: number;
    payment_count: number;
    member: {
        id: number;
        name: string;
        member_id: string;
    };
}

interface Props {
    metrics: Metrics;
    revenue_by_method: Record<string, number>;
    revenue_by_gateway: Record<string, number>;
    chart_data: {
        labels: string[];
        data: number[];
    };
    gateway_performance: Array<{
        payment_gateway: string;
        total: number;
        completed: number;
        failed: number;
        success_rate: number;
    }>;
    recent_payments: Payment[];
    overdue_invoices: Invoice[];
    top_paying_members: TopPayingMember[];
    members_with_overdue: Array<{
        member_id: number;
        overdue_count: number;
        overdue_amount: number;
        member: {
            id: number;
            name: string;
            member_id: string;
        };
    }>;
    filters: {
        start_date: string;
        end_date: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Finance', href: '/finance' }];

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

watch(
    [startDate, endDate],
    () => {
        router.get(
            '/finance',
            {
                start_date: startDate.value,
                end_date: endDate.value,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    },
    { debounce: 300 },
);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
    }).format(amount / 100);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        completed:
            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        pending:
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        refunded:
            'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
        paid: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        sent: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
        overdue: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    };
    return (
        classes[status] ||
        'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'
    );
};
</script>

<template>
    <Head title="Finance Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Finance Dashboard
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Monitor revenue, payments, and financial metrics
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        href="/payments"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        <CreditCard class="h-4 w-4" />
                        View Payments
                    </Link>
                    <Link
                        href="/invoices"
                        class="inline-flex items-center gap-2 rounded-lg border border-sidebar-border/70 px-4 py-2 text-sm font-medium hover:bg-accent"
                    >
                        <FileText class="h-4 w-4" />
                        View Invoices
                    </Link>
                </div>
            </div>

            <!-- Date Range Filters -->
            <div class="flex items-end gap-4">
                <div class="max-w-xs flex-1">
                    <label class="mb-2 block text-sm font-medium"
                        >Start Date</label
                    >
                    <input
                        v-model="startDate"
                        type="date"
                        class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2 text-sm"
                    />
                </div>
                <div class="max-w-xs flex-1">
                    <label class="mb-2 block text-sm font-medium"
                        >End Date</label
                    >
                    <input
                        v-model="endDate"
                        type="date"
                        class="w-full rounded-lg border border-sidebar-border/70 bg-background px-4 py-2 text-sm"
                    />
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Revenue -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Total Revenue
                            </p>
                            <p class="mt-2 text-2xl font-bold">
                                {{ formatCurrency(metrics.total_revenue) }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ metrics.total_payments }} payments
                            </p>
                        </div>
                        <div
                            class="rounded-full bg-green-100 p-3 dark:bg-green-900/20"
                        >
                            <DollarSign
                                class="h-6 w-6 text-green-600 dark:text-green-400"
                            />
                        </div>
                    </div>
                </div>

                <!-- Monthly Revenue -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                This Month
                            </p>
                            <p class="mt-2 text-2xl font-bold">
                                {{ formatCurrency(metrics.month_revenue) }}
                            </p>
                            <p
                                class="mt-1 text-xs"
                                :class="
                                    metrics.month_growth >= 0
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-red-600 dark:text-red-400'
                                "
                            >
                                <span v-if="metrics.month_growth >= 0">+</span
                                >{{ metrics.month_growth }}% from last month
                            </p>
                        </div>
                        <div
                            class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20"
                        >
                            <TrendingUp
                                class="h-6 w-6 text-blue-600 dark:text-blue-400"
                            />
                        </div>
                    </div>
                </div>

                <!-- Outstanding Invoices -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Outstanding
                            </p>
                            <p class="mt-2 text-2xl font-bold">
                                {{ formatCurrency(metrics.outstanding_amount) }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ metrics.overdue_count }} overdue
                            </p>
                        </div>
                        <div
                            class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900/20"
                        >
                            <AlertCircle
                                class="h-6 w-6 text-yellow-600 dark:text-yellow-400"
                            />
                        </div>
                    </div>
                </div>

                <!-- Success Rate -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Success Rate
                            </p>
                            <p class="mt-2 text-2xl font-bold">
                                {{ metrics.success_rate }}%
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ metrics.completed_payments }} /
                                {{
                                    metrics.completed_payments +
                                    metrics.failed_payments
                                }}
                            </p>
                        </div>
                        <div
                            class="rounded-full bg-green-100 p-3 dark:bg-green-900/20"
                        >
                            <CheckCircle
                                class="h-6 w-6 text-green-600 dark:text-green-400"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gateway Performance -->
            <div
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h2 class="mb-4 text-lg font-semibold">Gateway Performance</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-sidebar-border/70">
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Gateway
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                                >
                                    Total
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                                >
                                    Completed
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                                >
                                    Failed
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                                >
                                    Success Rate
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="gateway in gateway_performance"
                                :key="gateway.payment_gateway"
                                class="border-b border-sidebar-border/70 last:border-0"
                            >
                                <td class="px-4 py-3 capitalize">
                                    {{ gateway.payment_gateway }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ gateway.total }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right text-green-600 dark:text-green-400"
                                >
                                    {{ gateway.completed }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right text-red-600 dark:text-red-400"
                                >
                                    {{ gateway.failed }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    {{ gateway.success_rate }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Payments & Overdue Invoices -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Recent Payments -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Recent Payments</h2>
                        <Link
                            href="/payments"
                            class="text-sm text-primary hover:underline"
                        >
                            View all
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div
                            v-for="payment in recent_payments"
                            :key="payment.id"
                            class="flex items-center justify-between border-b border-sidebar-border/70 py-3 last:border-0"
                        >
                            <div class="flex-1">
                                <p class="font-medium">
                                    {{ payment.member.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ payment.member.member_id }}
                                    <span v-if="payment.subscription">
                                        •
                                        {{
                                            payment.subscription.membership_plan
                                                .name
                                        }}</span
                                    >
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ formatDateTime(payment.payment_date) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">
                                    {{ formatCurrency(payment.amount) }}
                                </p>
                                <span
                                    class="mt-1 inline-block rounded-full px-2 py-1 text-xs"
                                    :class="getStatusBadgeClass(payment.status)"
                                >
                                    {{ payment.status }}
                                </span>
                            </div>
                        </div>
                        <div
                            v-if="recent_payments.length === 0"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No recent payments
                        </div>
                    </div>
                </div>

                <!-- Overdue Invoices -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Overdue Invoices</h2>
                        <Link
                            href="/invoices?status=overdue"
                            class="text-sm text-primary hover:underline"
                        >
                            View all
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div
                            v-for="invoice in overdue_invoices"
                            :key="invoice.id"
                            class="flex items-center justify-between border-b border-sidebar-border/70 py-3 last:border-0"
                        >
                            <div class="flex-1">
                                <p class="font-medium">
                                    {{ invoice.member.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ invoice.invoice_number }} •
                                    {{ invoice.member.member_id }}
                                </p>
                                <p
                                    class="mt-1 text-xs text-red-600 dark:text-red-400"
                                >
                                    {{ invoice.days_overdue }} days overdue
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">
                                    {{ formatCurrency(invoice.total_amount) }}
                                </p>
                                <span
                                    class="mt-1 inline-block rounded-full px-2 py-1 text-xs"
                                    :class="getStatusBadgeClass('overdue')"
                                >
                                    overdue
                                </span>
                            </div>
                        </div>
                        <div
                            v-if="overdue_invoices.length === 0"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No overdue invoices
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Paying Members -->
            <div
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h2 class="mb-4 text-lg font-semibold">Top Paying Members</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-sidebar-border/70">
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Member
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                                >
                                    Total Paid
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                                >
                                    Payments
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="member in top_paying_members"
                                :key="member.member_id"
                                class="border-b border-sidebar-border/70 last:border-0"
                            >
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-medium">
                                            {{ member.member.name }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ member.member.member_id }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">
                                    {{ formatCurrency(member.total_paid) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ member.payment_count }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
