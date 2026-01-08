<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Calendar, FileText, TrendingUp, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    reportType: string;
    startDate: string;
    endDate: string;
    reportData: any;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Reports', href: '/reports' }];

const selectedType = ref(props.reportType);
const startDate = ref(props.startDate);
const endDate = ref(props.endDate);

const reportTypes = [
    { value: 'attendance', label: 'Attendance', icon: Users },
    { value: 'revenue', label: 'Revenue', icon: TrendingUp },
    { value: 'members', label: 'Members', icon: FileText },
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

const applyFilters = () => {
    router.get(
        '/reports',
        {
            type: selectedType.value,
            start_date: startDate.value,
            end_date: endDate.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const currentIcon = computed(() => {
    const type = reportTypes.find((t) => t.value === selectedType.value);
    return type?.icon || FileText;
});
</script>

<template>
    <Head title="Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Reports</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    View detailed reports for your gym operations
                </p>
            </div>

            <!-- Filters -->
            <div
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <div class="grid gap-4 md:grid-cols-4">
                    <!-- Report Type -->
                    <div>
                        <label class="mb-2 block text-sm font-medium"
                            >Report Type</label
                        >
                        <select
                            v-model="selectedType"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            <option
                                v-for="type in reportTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="mb-2 block text-sm font-medium"
                            >Start Date</label
                        >
                        <input
                            v-model="startDate"
                            type="date"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        />
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="mb-2 block text-sm font-medium"
                            >End Date</label
                        >
                        <input
                            v-model="endDate"
                            type="date"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        />
                    </div>

                    <!-- Apply Button -->
                    <div class="flex items-end">
                        <button
                            @click="applyFilters"
                            class="w-full rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Attendance Report -->
            <div v-if="reportType === 'attendance'" class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid gap-4 md:grid-cols-4">
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Total Check-ins
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ reportData.summary.total_check_ins }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Unique Members
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ reportData.summary.unique_members }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Avg Check-ins/Day
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ reportData.summary.avg_check_ins_per_day }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Date Range
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ reportData.summary.date_range_days }} days
                        </p>
                    </div>
                </div>

                <!-- Top Members & Peak Hours -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Top Members -->
                    <div
                        class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                    >
                        <div
                            class="border-b border-sidebar-border/50 px-6 py-4"
                        >
                            <h2 class="text-lg font-semibold">
                                Top Members by Check-ins
                            </h2>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="reportData.top_members.length > 0"
                                class="space-y-3"
                            >
                                <div
                                    v-for="(
                                        item, index
                                    ) in reportData.top_members"
                                    :key="item.member.id"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary"
                                        >
                                            {{ index + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-medium">
                                                {{ item.member.name }}
                                            </p>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{ item.member.member_id }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-lg font-bold">{{
                                        item.check_in_count
                                    }}</span>
                                </div>
                            </div>
                            <p
                                v-else
                                class="py-8 text-center text-muted-foreground"
                            >
                                No data available
                            </p>
                        </div>
                    </div>

                    <!-- Peak Hours -->
                    <div
                        class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                    >
                        <div
                            class="border-b border-sidebar-border/50 px-6 py-4"
                        >
                            <h2 class="text-lg font-semibold">Peak Hours</h2>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="reportData.peak_hours.length > 0"
                                class="space-y-3"
                            >
                                <div
                                    v-for="item in reportData.peak_hours"
                                    :key="item.hour"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-3">
                                        <Calendar
                                            class="h-5 w-5 text-muted-foreground"
                                        />
                                        <span class="font-medium">{{
                                            item.hour
                                        }}</span>
                                    </div>
                                    <span class="text-lg font-bold"
                                        >{{ item.count }} check-ins</span
                                    >
                                </div>
                            </div>
                            <p
                                v-else
                                class="py-8 text-center text-muted-foreground"
                            >
                                No data available
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Report -->
            <div v-if="reportType === 'revenue'" class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid gap-4 md:grid-cols-3">
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Total Revenue
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{
                                formatCurrency(reportData.summary.total_revenue)
                            }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Total Payments
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ reportData.summary.total_payments }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Avg Payment
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{
                                formatCurrency(
                                    reportData.summary.avg_payment_amount,
                                )
                            }}
                        </p>
                    </div>
                </div>

                <!-- Revenue by Method & Top Payers -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Revenue by Method -->
                    <div
                        class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                    >
                        <div
                            class="border-b border-sidebar-border/50 px-6 py-4"
                        >
                            <h2 class="text-lg font-semibold">
                                Revenue by Payment Method
                            </h2>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="reportData.revenue_by_method.length > 0"
                                class="space-y-3"
                            >
                                <div
                                    v-for="method in reportData.revenue_by_method"
                                    :key="method.payment_method"
                                    class="flex items-center justify-between"
                                >
                                    <div>
                                        <p class="font-medium capitalize">
                                            {{ method.payment_method }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ method.count }} payments
                                        </p>
                                    </div>
                                    <span class="text-lg font-bold">{{
                                        formatCurrency(method.total)
                                    }}</span>
                                </div>
                            </div>
                            <p
                                v-else
                                class="py-8 text-center text-muted-foreground"
                            >
                                No data available
                            </p>
                        </div>
                    </div>

                    <!-- Top Paying Members -->
                    <div
                        class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                    >
                        <div
                            class="border-b border-sidebar-border/50 px-6 py-4"
                        >
                            <h2 class="text-lg font-semibold">
                                Top Paying Members
                            </h2>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="reportData.top_paying_members.length > 0"
                                class="space-y-3"
                            >
                                <div
                                    v-for="(
                                        item, index
                                    ) in reportData.top_paying_members.slice(
                                        0,
                                        5,
                                    )"
                                    :key="item.member.id"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary"
                                        >
                                            {{ index + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-medium">
                                                {{ item.member.name }}
                                            </p>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{
                                                    item.payment_count
                                                }}
                                                payments
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-lg font-bold">{{
                                        formatCurrency(item.total_paid)
                                    }}</span>
                                </div>
                            </div>
                            <p
                                v-else
                                class="py-8 text-center text-muted-foreground"
                            >
                                No data available
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Members Report -->
            <div v-if="reportType === 'members'" class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid gap-4 md:grid-cols-4">
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            New Members
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ reportData.summary.new_members }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Active Members
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ reportData.summary.active_members }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Inactive Members
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ reportData.summary.inactive_members }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium text-muted-foreground">
                            Total Members
                        </p>
                        <p class="mt-2 text-2xl font-bold">
                            {{ reportData.summary.total_members }}
                        </p>
                    </div>
                </div>

                <!-- Recent Members -->
                <div
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div class="border-b border-sidebar-border/50 px-6 py-4">
                        <h2 class="text-lg font-semibold">
                            Recent Member Registrations
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table
                            v-if="reportData.recent_members.length > 0"
                            class="w-full text-sm"
                        >
                            <thead
                                class="border-b border-sidebar-border bg-sidebar"
                            >
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Name
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Member ID
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Email
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Registered
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sidebar-border/50">
                                <tr
                                    v-for="member in reportData.recent_members"
                                    :key="member.id"
                                    class="transition-colors hover:bg-sidebar/50"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ member.name }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ member.member_id }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ member.email }}
                                    </td>
                                    <td class="px-4 py-3">
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
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ formatDate(member.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p
                            v-else
                            class="py-8 text-center text-muted-foreground"
                        >
                            No members registered in this period
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
