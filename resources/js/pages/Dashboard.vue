<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Users, UserCheck, DollarSign, TrendingUp, Calendar, AlertCircle } from 'lucide-vue-next';

interface Metrics {
    active_members: number;
    total_members: number;
    today_check_ins: number;
    month_check_ins: number;
    today_revenue: number;
    month_revenue: number;
    active_subscriptions: number;
    expiring_soon: number;
}

interface RecentMember {
    id: number;
    name: string;
    member_id: string;
    email: string;
    status: string;
    created_at: string;
    subscription: any;
}

interface Charts {
    revenue_by_day: {
        labels: string[];
        data: number[];
    };
    check_ins_by_day: {
        labels: string[];
        data: number[];
    };
}

interface Props {
    metrics: Metrics;
    recent_members: RecentMember[];
    charts: Charts;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

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
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Welcome back! Here's what's happening with your gym today.
                </p>
            </div>

            <!-- Key Metrics -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Active Members -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Active Members</p>
                            <p class="text-2xl font-bold mt-2">{{ metrics.active_members }}</p>
                            <p class="text-xs text-muted-foreground mt-1">
                                of {{ metrics.total_members }} total
                            </p>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                            <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <!-- Today's Check-ins -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Today's Check-ins</p>
                            <p class="text-2xl font-bold mt-2">{{ metrics.today_check_ins }}</p>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ metrics.month_check_ins }} this month
                            </p>
                        </div>
                        <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                            <UserCheck class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <!-- Today's Revenue -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Today's Revenue</p>
                            <p class="text-2xl font-bold mt-2">{{ formatCurrency(metrics.today_revenue) }}</p>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ formatCurrency(metrics.month_revenue) }} this month
                            </p>
                        </div>
                        <div class="rounded-full bg-emerald-100 p-3 dark:bg-emerald-900/20">
                            <DollarSign class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                        </div>
                    </div>
                </div>

                <!-- Active Subscriptions -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Active Subscriptions</p>
                            <p class="text-2xl font-bold mt-2">{{ metrics.active_subscriptions }}</p>
                            <p v-if="metrics.expiring_soon > 0" class="text-xs text-orange-600 dark:text-orange-400 mt-1 flex items-center gap-1">
                                <AlertCircle class="h-3 w-3" />
                                {{ metrics.expiring_soon }} expiring soon
                            </p>
                            <p v-else class="text-xs text-muted-foreground mt-1">
                                All good
                            </p>
                        </div>
                        <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                            <Calendar class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Recent Members -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                    <div class="border-b border-sidebar-border/50 px-6 py-4">
                        <h2 class="text-lg font-semibold">Recent Members</h2>
                        <p class="text-sm text-muted-foreground">Latest member registrations</p>
                    </div>
                    <div class="p-6">
                        <div v-if="recent_members.length > 0" class="space-y-4">
                            <div
                                v-for="member in recent_members.slice(0, 5)"
                                :key="member.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex-1">
                                    <Link
                                        :href="`/members/${member.id}`"
                                        class="font-medium hover:text-primary"
                                    >
                                        {{ member.name }}
                                    </Link>
                                    <p class="text-sm text-muted-foreground">{{ member.member_id }}</p>
                                </div>
                                <div class="text-right">
                                    <span
                                        :class="[
                                            member.status === 'active'
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                                        ]"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                    >
                                        {{ member.status }}
                                    </span>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        {{ formatDate(member.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <Link
                                href="/members"
                                class="block text-center text-sm text-primary hover:text-primary/80 pt-4 border-t border-sidebar-border/50"
                            >
                                View all members →
                            </Link>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            No members registered yet
                        </div>
                    </div>
                </div>

                <!-- Revenue Chart (Simple List View) -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                    <div class="border-b border-sidebar-border/50 px-6 py-4">
                        <h2 class="text-lg font-semibold">Revenue (Last 7 Days)</h2>
                        <p class="text-sm text-muted-foreground">Daily revenue overview</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div
                                v-for="(amount, index) in charts.revenue_by_day.data"
                                :key="index"
                                class="flex items-center justify-between"
                            >
                                <span class="text-sm text-muted-foreground">
                                    {{ new Date(charts.revenue_by_day.labels[index]).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) }}
                                </span>
                                <span class="font-medium">{{ formatCurrency(amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check-ins Chart (Simple List View) -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                    <div class="border-b border-sidebar-border/50 px-6 py-4">
                        <h2 class="text-lg font-semibold">Check-ins (Last 7 Days)</h2>
                        <p class="text-sm text-muted-foreground">Daily check-in trends</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div
                                v-for="(count, index) in charts.check_ins_by_day.data"
                                :key="index"
                                class="flex items-center justify-between"
                            >
                                <span class="text-sm text-muted-foreground">
                                    {{ new Date(charts.check_ins_by_day.labels[index]).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) }}
                                </span>
                                <div class="flex items-center gap-3">
                                    <div class="h-2 rounded-full bg-blue-200 dark:bg-blue-900" :style="{ width: `${Math.max(count / Math.max(...charts.check_ins_by_day.data) * 100, 5)}px` }" />
                                    <span class="font-medium w-8 text-right">{{ count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                    <div class="border-b border-sidebar-border/50 px-6 py-4">
                        <h2 class="text-lg font-semibold">Quick Actions</h2>
                        <p class="text-sm text-muted-foreground">Common tasks</p>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-3">
                            <Link
                                href="/members/create"
                                class="flex items-center gap-3 rounded-lg border border-sidebar-border/50 p-4 hover:bg-sidebar-accent transition-colors"
                            >
                                <Users class="h-5 w-5 text-muted-foreground" />
                                <span class="font-medium">Add New Member</span>
                            </Link>
                            <Link
                                href="/attendance/scan"
                                class="flex items-center gap-3 rounded-lg border border-sidebar-border/50 p-4 hover:bg-sidebar-accent transition-colors"
                            >
                                <UserCheck class="h-5 w-5 text-muted-foreground" />
                                <span class="font-medium">QR Code Check-in</span>
                            </Link>
                            <Link
                                href="/invoices"
                                class="flex items-center gap-3 rounded-lg border border-sidebar-border/50 p-4 hover:bg-sidebar-accent transition-colors"
                            >
                                <DollarSign class="h-5 w-5 text-muted-foreground" />
                                <span class="font-medium">View Invoices</span>
                            </Link>
                            <Link
                                href="/attendance"
                                class="flex items-center gap-3 rounded-lg border border-sidebar-border/50 p-4 hover:bg-sidebar-accent transition-colors"
                            >
                                <TrendingUp class="h-5 w-5 text-muted-foreground" />
                                <span class="font-medium">View Attendance</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
