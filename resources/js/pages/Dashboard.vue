<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Users, UserCheck, DollarSign, TrendingUp, Calendar, AlertCircle, UserPlus, QrCode, FileText, Activity, ArrowUpRight } from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

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
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground mt-1">
                        Welcome back! Here's what's happening with your gym today.
                    </p>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Active Members -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Active Members
                        </CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ metrics.active_members }}</div>
                        <p class="text-xs text-muted-foreground">
                            of {{ metrics.total_members }} total members
                        </p>
                    </CardContent>
                </Card>

                <!-- Today's Check-ins -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Today's Check-ins
                        </CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ metrics.today_check_ins }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ metrics.month_check_ins }} this month
                        </p>
                    </CardContent>
                </Card>

                <!-- Today's Revenue -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Today's Revenue
                        </CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(metrics.today_revenue) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ formatCurrency(metrics.month_revenue) }} this month
                        </p>
                    </CardContent>
                </Card>

                <!-- Active Subscriptions -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            Active Subscriptions
                        </CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ metrics.active_subscriptions }}</div>
                        <p v-if="metrics.expiring_soon > 0" class="text-xs text-orange-600 dark:text-orange-400 flex items-center gap-1">
                            <AlertCircle class="h-3 w-3" />
                            {{ metrics.expiring_soon }} expiring soon
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            All subscriptions active
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Recent Members -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Members</CardTitle>
                        <CardDescription>Latest member registrations</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recent_members.length > 0" class="space-y-6">
                            <div
                                v-for="member in recent_members.slice(0, 5)"
                                :key="member.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <span class="text-sm font-semibold text-primary">
                                            {{ member.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <Link
                                            :href="`/members/${member.id}`"
                                            class="font-medium hover:text-primary transition-colors"
                                        >
                                            {{ member.name }}
                                        </Link>
                                        <p class="text-sm text-muted-foreground">{{ member.member_id }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <Badge :variant="member.status === 'active' ? 'default' : 'secondary'">
                                        {{ member.status }}
                                    </Badge>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        {{ formatDate(member.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <Button as-child variant="ghost" class="w-full">
                                <Link href="/members" class="flex items-center gap-2">
                                    View all members
                                    <ArrowUpRight class="h-4 w-4" />
                                </Link>
                            </Button>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <Users class="h-12 w-12 mx-auto mb-2 opacity-20" />
                            <p>No members registered yet</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Revenue Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Revenue Overview</CardTitle>
                        <CardDescription>Last 7 days</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="(amount, index) in charts.revenue_by_day.data"
                                :key="index"
                                class="flex items-center justify-between"
                            >
                                <span class="text-sm text-muted-foreground">
                                    {{ new Date(charts.revenue_by_day.labels[index]).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) }}
                                </span>
                                <div class="flex items-center gap-3">
                                    <div class="h-2 w-24 rounded-full bg-muted overflow-hidden">
                                        <div
                                            class="h-full bg-primary rounded-full transition-all"
                                            :style="{ width: `${(amount / Math.max(...charts.revenue_by_day.data)) * 100}%` }"
                                        />
                                    </div>
                                    <span class="font-medium w-20 text-right">{{ formatCurrency(amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Check-ins Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Attendance Trends</CardTitle>
                        <CardDescription>Last 7 days check-ins</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="(count, index) in charts.check_ins_by_day.data"
                                :key="index"
                                class="flex items-center justify-between"
                            >
                                <span class="text-sm text-muted-foreground">
                                    {{ new Date(charts.check_ins_by_day.labels[index]).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) }}
                                </span>
                                <div class="flex items-center gap-3">
                                    <div class="h-2 w-24 rounded-full bg-muted overflow-hidden">
                                        <div
                                            class="h-full bg-blue-600 rounded-full transition-all"
                                            :style="{ width: `${(count / Math.max(...charts.check_ins_by_day.data)) * 100}%` }"
                                        />
                                    </div>
                                    <span class="font-medium w-12 text-right">{{ count }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Actions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common tasks and shortcuts</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-2">
                        <Button as-child variant="outline" class="justify-start h-auto py-3">
                            <Link href="/members/create" class="flex items-center gap-3">
                                <div class="rounded-lg bg-primary/10 p-2">
                                    <UserPlus class="h-4 w-4 text-primary" />
                                </div>
                                <div class="text-left">
                                    <div class="font-medium">Add New Member</div>
                                    <div class="text-xs text-muted-foreground">Register a new gym member</div>
                                </div>
                            </Link>
                        </Button>
                        <Button as-child variant="outline" class="justify-start h-auto py-3">
                            <Link href="/attendance/scan" class="flex items-center gap-3">
                                <div class="rounded-lg bg-green-500/10 p-2">
                                    <QrCode class="h-4 w-4 text-green-600 dark:text-green-500" />
                                </div>
                                <div class="text-left">
                                    <div class="font-medium">QR Code Check-in</div>
                                    <div class="text-xs text-muted-foreground">Scan member QR codes</div>
                                </div>
                            </Link>
                        </Button>
                        <Button as-child variant="outline" class="justify-start h-auto py-3">
                            <Link href="/invoices" class="flex items-center gap-3">
                                <div class="rounded-lg bg-orange-500/10 p-2">
                                    <FileText class="h-4 w-4 text-orange-600 dark:text-orange-500" />
                                </div>
                                <div class="text-left">
                                    <div class="font-medium">View Invoices</div>
                                    <div class="text-xs text-muted-foreground">Manage billing and invoices</div>
                                </div>
                            </Link>
                        </Button>
                        <Button as-child variant="outline" class="justify-start h-auto py-3">
                            <Link href="/attendance" class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-500/10 p-2">
                                    <Activity class="h-4 w-4 text-blue-600 dark:text-blue-500" />
                                </div>
                                <div class="text-left">
                                    <div class="font-medium">View Attendance</div>
                                    <div class="text-xs text-muted-foreground">Check attendance records</div>
                                </div>
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
