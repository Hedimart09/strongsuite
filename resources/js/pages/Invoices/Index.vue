<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Member {
    id: number;
    name: string;
    member_id: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    total_amount: number;
    currency: string;
    issue_date: string;
    due_date: string;
    status: string;
    member: Member;
}

interface Props {
    invoices: {
        data: Invoice[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        status?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/invoices' },
];

const status = ref(props.filters.status || '');
const search = ref(props.filters.search || '');

watch([status, search], () => {
    const params: Record<string, string> = {};
    if (status.value) params.status = status.value;
    if (search.value) params.search = search.value;

    router.get('/invoices', params, {
        preserveState: true,
        preserveScroll: true,
    });
}, { debounce: 300 });

const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount / 100);
};

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        sent: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
        overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || classes.draft;
};
</script>

<template>
    <Head title="Invoices" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Invoices</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Manage and track all invoices
                </p>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="status" class="block text-sm font-medium mb-2">Status</label>
                        <select
                            id="status"
                            v-model="status"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium mb-2">Search</label>
                        <input
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Search by invoice number or member..."
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-sidebar-border bg-sidebar">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Invoice #</th>
                                <th class="px-4 py-3 text-left font-medium">Member</th>
                                <th class="px-4 py-3 text-left font-medium">Issue Date</th>
                                <th class="px-4 py-3 text-left font-medium">Due Date</th>
                                <th class="px-4 py-3 text-left font-medium">Amount</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/50">
                            <tr
                                v-for="invoice in invoices.data"
                                :key="invoice.id"
                                class="hover:bg-sidebar/50 transition-colors"
                            >
                                <td class="px-4 py-3 font-mono text-xs">{{ invoice.invoice_number }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ invoice.member.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ invoice.member.member_id }}</div>
                                </td>
                                <td class="px-4 py-3">{{ new Date(invoice.issue_date).toLocaleDateString() }}</td>
                                <td class="px-4 py-3">{{ new Date(invoice.due_date).toLocaleDateString() }}</td>
                                <td class="px-4 py-3 font-medium">{{ formatCurrency(invoice.total_amount, invoice.currency) }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="getStatusClass(invoice.status)"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                    >
                                        {{ invoice.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a
                                            :href="`/invoices/${invoice.id}`"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            View
                                        </a>
                                        <a
                                            :href="`/invoices/${invoice.id}/pdf`"
                                            target="_blank"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="invoices.data.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                    No invoices found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="invoices.last_page > 1"
                    class="border-t border-sidebar-border px-4 py-3 flex items-center justify-between"
                >
                    <div class="text-sm text-muted-foreground">
                        Showing {{ ((invoices.current_page - 1) * invoices.per_page) + 1 }}
                        to {{ Math.min(invoices.current_page * invoices.per_page, invoices.total) }}
                        of {{ invoices.total }} records
                    </div>
                    <div class="flex gap-2">
                        <a
                            v-for="(link, index) in invoices.links"
                            :key="index"
                            :href="link.url"
                            :class="[
                                link.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-sidebar hover:bg-sidebar-accent',
                                !link.url && 'opacity-50 cursor-not-allowed',
                            ]"
                            class="px-3 py-1 rounded text-sm"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
