<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface Member {
    id: number;
    name: string;
    member_id: string;
    email: string;
}

interface MembershipPlan {
    id: number;
    name: string;
}

interface Subscription {
    id: number;
    start_date: string;
    end_date: string;
    membership_plan: MembershipPlan;
}

interface Payment {
    id: number;
    amount: number;
    currency: string;
    payment_method: string;
    payment_date: string;
    status: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    total_amount: number;
    currency: string;
    issue_date: string;
    due_date: string;
    status: string;
    paid_at: string | null;
    member: Member;
    subscription: Subscription | null;
    payments: Payment[];
}

interface Props {
    invoice: Invoice;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/invoices' },
    {
        title: props.invoice.invoice_number,
        href: `/invoices/${props.invoice.id}`,
    },
];

const formatAmount = (amount: number, currency: string) => {
    return new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: currency,
    }).format(amount / 100);
};

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
        sent: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        cancelled:
            'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    };
    return classes[status] || classes.draft;
};
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ invoice.invoice_number }}
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Invoice for {{ invoice.member.name }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <a
                        :href="`/invoices/${invoice.id}/download`"
                        class="rounded-lg border border-input px-4 py-2 text-sm font-medium hover:bg-accent"
                    >
                        Download PDF
                    </a>
                    <Link
                        v-if="invoice.status !== 'paid'"
                        :href="`/invoices/${invoice.id}/mark-as-paid`"
                        method="post"
                        as="button"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                    >
                        Mark as Paid
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Invoice Details -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Main Info -->
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2 class="mb-4 text-lg font-semibold">
                            Invoice Details
                        </h2>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Invoice Number
                                </p>
                                <p class="font-medium">
                                    {{ invoice.invoice_number }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Status
                                </p>
                                <span
                                    :class="getStatusClass(invoice.status)"
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                >
                                    {{ invoice.status }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Issue Date
                                </p>
                                <p class="font-medium">
                                    {{
                                        new Date(
                                            invoice.issue_date,
                                        ).toLocaleDateString()
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Due Date
                                </p>
                                <p class="font-medium">
                                    {{
                                        new Date(
                                            invoice.due_date,
                                        ).toLocaleDateString()
                                    }}
                                </p>
                            </div>
                            <div v-if="invoice.paid_at">
                                <p class="text-sm text-muted-foreground">
                                    Paid On
                                </p>
                                <p class="font-medium">
                                    {{
                                        new Date(
                                            invoice.paid_at,
                                        ).toLocaleDateString()
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Total Amount
                                </p>
                                <p class="text-lg font-bold">
                                    {{
                                        formatAmount(
                                            invoice.total_amount,
                                            invoice.currency,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Info -->
                    <div
                        v-if="invoice.subscription"
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2 class="mb-4 text-lg font-semibold">
                            Subscription Details
                        </h2>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Plan
                                </p>
                                <p class="font-medium">
                                    {{
                                        invoice.subscription.membership_plan
                                            .name
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Period
                                </p>
                                <p class="font-medium">
                                    {{
                                        new Date(
                                            invoice.subscription.start_date,
                                        ).toLocaleDateString()
                                    }}
                                    -
                                    {{
                                        new Date(
                                            invoice.subscription.end_date,
                                        ).toLocaleDateString()
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Payments -->
                    <div
                        v-if="invoice.payments.length > 0"
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2 class="mb-4 text-lg font-semibold">Payments</h2>
                        <div class="space-y-3">
                            <div
                                v-for="payment in invoice.payments"
                                :key="payment.id"
                                class="flex items-center justify-between rounded-lg border border-sidebar-border/50 p-3"
                            >
                                <div>
                                    <p class="font-medium">
                                        {{
                                            formatAmount(
                                                payment.amount,
                                                payment.currency,
                                            )
                                        }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ payment.payment_method }} •
                                        {{
                                            new Date(
                                                payment.payment_date,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                                <span
                                    :class="
                                        payment.status === 'completed'
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'
                                    "
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                >
                                    {{ payment.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Member Info Sidebar -->
                <div class="lg:col-span-1">
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <h2 class="mb-4 text-lg font-semibold">
                            Member Information
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Name
                                </p>
                                <Link
                                    :href="`/members/${invoice.member.id}`"
                                    class="font-medium text-primary hover:text-primary/80"
                                >
                                    {{ invoice.member.name }}
                                </Link>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Member ID
                                </p>
                                <p class="font-medium">
                                    {{ invoice.member.member_id }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Email
                                </p>
                                <p class="font-medium">
                                    {{ invoice.member.email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
