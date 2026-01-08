<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Subscription {
    id: number;
    start_date: string;
    end_date: string;
    status: string;
    member: {
        id: number;
        name: string;
        member_id: string;
    };
}

interface MembershipPlan {
    id: number;
    name: string;
    description: string | null;
    price: number;
    currency: string;
    duration_in_days: number;
    is_active: boolean;
    features: string[] | null;
    subscriptions_count: number;
    subscriptions: Subscription[];
    created_at: string;
}

interface Props {
    plan: MembershipPlan;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Membership Plans', href: '/membership-plans' },
    { title: props.plan.name, href: `/membership-plans/${props.plan.id}` },
];

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

const getStatusClass = (status: string) => {
    return status === 'active'
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
};
</script>

<template>
    <Head :title="plan.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ plan.name }}
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ plan.subscriptions_count }} active
                        {{
                            plan.subscriptions_count === 1
                                ? 'subscription'
                                : 'subscriptions'
                        }}
                    </p>
                    <div class="mt-2">
                        <span
                            :class="
                                getStatusClass(
                                    plan.is_active ? 'active' : 'inactive',
                                )
                            "
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                        >
                            {{ plan.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <a
                    :href="`/membership-plans/${plan.id}/edit`"
                    class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90"
                >
                    Edit Plan
                </a>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 text-lg font-semibold">Plan Details</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Price
                            </dt>
                            <dd class="mt-1 text-2xl font-bold">
                                {{ formatCurrency(plan.price, plan.currency) }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Duration
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{ formatDuration(plan.duration_in_days) }}
                            </dd>
                        </div>
                        <div v-if="plan.description">
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Description
                            </dt>
                            <dd class="mt-1 text-sm">{{ plan.description }}</dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Created
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{
                                    new Date(
                                        plan.created_at,
                                    ).toLocaleDateString()
                                }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 text-lg font-semibold">Features</h2>
                    <ul
                        v-if="plan.features && plan.features.length > 0"
                        class="space-y-2"
                    >
                        <li
                            v-for="(feature, index) in plan.features"
                            :key="index"
                            class="flex items-start gap-2"
                        >
                            <svg
                                class="mt-0.5 h-5 w-5 text-primary"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            <span class="text-sm">{{ feature }}</span>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">
                        No features listed
                    </p>
                </div>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h2 class="mb-4 text-lg font-semibold">Recent Subscriptions</h2>
                <div
                    v-if="plan.subscriptions.length > 0"
                    class="overflow-x-auto"
                >
                    <table class="w-full text-sm">
                        <thead class="border-b border-sidebar-border">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium">
                                    Member
                                </th>
                                <th class="px-4 py-2 text-left font-medium">
                                    Start Date
                                </th>
                                <th class="px-4 py-2 text-left font-medium">
                                    End Date
                                </th>
                                <th class="px-4 py-2 text-left font-medium">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/50">
                            <tr
                                v-for="subscription in plan.subscriptions"
                                :key="subscription.id"
                            >
                                <td class="px-4 py-3">
                                    <a
                                        :href="`/members/${subscription.member.id}`"
                                        class="text-primary hover:text-primary/80"
                                    >
                                        {{ subscription.member.name }}
                                    </a>
                                    <div class="text-xs text-muted-foreground">
                                        {{ subscription.member.member_id }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    {{
                                        new Date(
                                            subscription.start_date,
                                        ).toLocaleDateString()
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    {{
                                        new Date(
                                            subscription.end_date,
                                        ).toLocaleDateString()
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="
                                            getStatusClass(subscription.status)
                                        "
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ subscription.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-muted-foreground">
                    No subscriptions yet
                </p>
            </div>
        </div>
    </AppLayout>
</template>
