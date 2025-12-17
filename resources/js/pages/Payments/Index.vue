<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { CreditCard, Search, Filter } from 'lucide-vue-next';
import { ref, watch } from 'vue';

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
    invoice: {
        id: number;
        invoice_number: string;
    } | null;
}

interface Props {
    payments: {
        data: Payment[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        status?: string;
        payment_method?: string;
        start_date?: string;
        end_date?: string;
        member_id?: number;
        search?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Payments', href: '/payments' },
];

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const paymentMethod = ref(props.filters.payment_method || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

watch([search, status, paymentMethod, startDate, endDate], () => {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (status.value) params.status = status.value;
    if (paymentMethod.value) params.payment_method = paymentMethod.value;
    if (startDate.value) params.start_date = startDate.value;
    if (endDate.value) params.end_date = endDate.value;

    router.get('/payments', params, {
        preserveState: true,
        preserveScroll: true,
    });
}, { debounce: 300 });

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
    }).format(amount / 100);
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusClass = (paymentStatus: string) => {
    const classes: Record<string, string> = {
        completed: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        refunded: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
    };
    return classes[paymentStatus] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
};

const getMethodBadgeClass = (method: string) => {
    const classes: Record<string, string> = {
        paystack: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
        flutterwave: 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
        stripe: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
        cash: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        mobile_money: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        bank_transfer: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400',
    };
    return classes[method] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
};
</script>

<template>
    <Head title="Payments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Payments</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        View and manage all payment transactions
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        href="/finance"
                        class="inline-flex items-center gap-2 rounded-lg border border-sidebar-border/70 px-4 py-2 text-sm font-medium hover:bg-accent"
                    >
                        Finance Dashboard
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <label for="search" class="block text-sm font-medium mb-2">
                            <Search class="inline h-4 w-4 mr-1" />
                            Search
                        </label>
                        <input
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Search by transaction ID or member name..."
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium mb-2">Status</label>
                        <select
                            id="status"
                            v-model="status"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium mb-2">Payment Method</label>
                        <select
                            id="payment_method"
                            v-model="paymentMethod"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="">All Methods</option>
                            <option value="paystack">Paystack</option>
                            <option value="flutterwave">Flutterwave</option>
                            <option value="stripe">Stripe</option>
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 mt-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium mb-2">Start Date</label>
                        <input
                            id="start_date"
                            v-model="startDate"
                            type="date"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium mb-2">End Date</label>
                        <input
                            id="end_date"
                            v-model="endDate"
                            type="date"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-sidebar-border/70 p-4">
                    <p class="text-sm text-muted-foreground">Total Payments</p>
                    <p class="text-2xl font-bold mt-1">{{ payments.total }}</p>
                </div>
                <div class="rounded-lg border border-sidebar-border/70 p-4">
                    <p class="text-sm text-muted-foreground">Completed</p>
                    <p class="text-2xl font-bold mt-1 text-green-600 dark:text-green-400">
                        {{ payments.data.filter(p => p.status === 'completed').length }}
                    </p>
                </div>
                <div class="rounded-lg border border-sidebar-border/70 p-4">
                    <p class="text-sm text-muted-foreground">Pending</p>
                    <p class="text-2xl font-bold mt-1 text-yellow-600 dark:text-yellow-400">
                        {{ payments.data.filter(p => p.status === 'pending').length }}
                    </p>
                </div>
                <div class="rounded-lg border border-sidebar-border/70 p-4">
                    <p class="text-sm text-muted-foreground">Failed</p>
                    <p class="text-2xl font-bold mt-1 text-red-600 dark:text-red-400">
                        {{ payments.data.filter(p => p.status === 'failed').length }}
                    </p>
                </div>
            </div>

            <!-- Payments List -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-sidebar-border bg-sidebar">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Transaction</th>
                                <th class="px-4 py-3 text-left font-medium">Member</th>
                                <th class="px-4 py-3 text-left font-medium">Amount</th>
                                <th class="px-4 py-3 text-left font-medium">Method</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">Date</th>
                                <th class="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/50">
                            <tr
                                v-for="payment in payments.data"
                                :key="payment.id"
                                class="hover:bg-sidebar/50 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-medium">{{ payment.transaction_id || 'N/A' }}</p>
                                        <p class="text-xs text-muted-foreground" v-if="payment.subscription">
                                            {{ payment.subscription.membership_plan.name }}
                                        </p>
                                        <p class="text-xs text-muted-foreground" v-if="payment.invoice">
                                            {{ payment.invoice.invoice_number }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-medium">{{ payment.member.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ payment.member.member_id }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ formatCurrency(payment.amount) }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium capitalize"
                                        :class="getMethodBadgeClass(payment.payment_method)"
                                    >
                                        {{ payment.payment_method.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium capitalize"
                                        :class="getStatusClass(payment.status)"
                                    >
                                        {{ payment.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm">{{ formatDateTime(payment.payment_date) }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <Link
                                        :href="`/payments/${payment.id}`"
                                        class="text-sm text-primary hover:underline"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- No Results -->
                <div v-if="payments.data.length === 0" class="text-center py-12">
                    <CreditCard class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                    <p class="text-muted-foreground">No payments found</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="payments.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    Showing {{ (payments.current_page - 1) * payments.per_page + 1 }} to
                    {{ Math.min(payments.current_page * payments.per_page, payments.total) }} of
                    {{ payments.total }} results
                </p>
                <div class="flex gap-2">
                    <Link
                        v-for="(link, index) in payments.links"
                        :key="index"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-2 text-sm rounded-lg border',
                            link.active
                                ? 'bg-primary text-primary-foreground border-primary'
                                : 'border-sidebar-border/70 hover:bg-accent',
                            !link.url && 'opacity-50 cursor-not-allowed',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
