<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface MembershipPlan {
    id: number;
    name: string;
    description: string | null;
    price: number;
    currency: string;
    duration_in_days: number;
    is_active: boolean;
    subscriptions_count: number;
    created_at: string;
}

interface Props {
    plans: {
        data: MembershipPlan[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Membership Plans',
        href: '/membership-plans',
    },
];

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

watch([search, status], () => {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (status.value) params.status = status.value;

    router.get('/membership-plans', params, {
        preserveState: true,
        preserveScroll: true,
    });
}, { debounce: 300 });

const getStatusClass = (planStatus: boolean) => {
    return planStatus
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
};

const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount / 100);
};

const formatDuration = (days: number) => {
    if (days === 1) return '1 day';
    if (days === 7) return '1 week';
    if (days === 30) return '1 month';
    if (days === 90) return '3 months';
    if (days === 180) return '6 months';
    if (days === 365) return '1 year';
    return `${days} days`;
};
</script>

<template>
    <Head title="Membership Plans" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Membership Plans</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage membership plans and pricing
                    </p>
                </div>
                <a
                    href="/membership-plans/create"
                    class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Create Plan
                </a>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium mb-2">Search</label>
                        <input
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Search by name or description..."
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
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Plans List -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-sidebar-border bg-sidebar">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Plan Name</th>
                                <th class="px-4 py-3 text-left font-medium">Price</th>
                                <th class="px-4 py-3 text-left font-medium">Duration</th>
                                <th class="px-4 py-3 text-left font-medium">Subscriptions</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/50">
                            <tr
                                v-for="plan in plans.data"
                                :key="plan.id"
                                class="hover:bg-sidebar/50 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <div>
                                        <div class="font-medium">{{ plan.name }}</div>
                                        <div v-if="plan.description" class="text-xs text-muted-foreground mt-1">
                                            {{ plan.description.substring(0, 60) }}{{ plan.description.length > 60 ? '...' : '' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ formatCurrency(plan.price, plan.currency) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ formatDuration(plan.duration_in_days) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">
                                        {{ plan.subscriptions_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="getStatusClass(plan.is_active)"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ plan.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a
                                            :href="`/membership-plans/${plan.id}`"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            View
                                        </a>
                                        <a
                                            :href="`/membership-plans/${plan.id}/edit`"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="plans.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                    No membership plans found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="plans.last_page > 1"
                    class="border-t border-sidebar-border px-4 py-3 flex items-center justify-between"
                >
                    <div class="text-sm text-muted-foreground">
                        Showing {{ ((plans.current_page - 1) * plans.per_page) + 1 }}
                        to {{ Math.min(plans.current_page * plans.per_page, plans.total) }}
                        of {{ plans.total }} plans
                    </div>
                    <div class="flex gap-2">
                        <a
                            v-for="(link, index) in plans.links"
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
