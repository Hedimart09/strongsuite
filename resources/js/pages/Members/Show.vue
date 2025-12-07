<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Subscription {
    id: number;
    start_date: string;
    end_date: string;
    status: string;
    membership_plan: {
        name: string;
        price: number;
        currency: string;
        duration_in_days: number;
    };
}

interface Payment {
    id: number;
    amount: number;
    currency: string;
    payment_method: string;
    status: string;
    payment_date: string;
}

interface Attendance {
    id: number;
    check_in_time: string;
    check_out_time: string | null;
    check_in_method: string;
}

interface Member {
    id: number;
    member_id: string;
    name: string;
    email: string;
    phone: string;
    photo: string | null;
    date_of_birth: string;
    gender: string;
    address: string | null;
    emergency_contact_name: string;
    emergency_contact_phone: string;
    qr_code: string;
    status: string;
    created_at: string;
    subscriptions: Subscription[];
    payments: Payment[];
    attendances: Attendance[];
}

interface Props {
    member: Member;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Members',
        href: '/members',
    },
    {
        title: props.member.name,
        href: `/members/${props.member.id}`,
    },
];

const getStatusClass = (status: string) => {
    return status === 'active'
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
};

const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount / 100);
};
</script>

<template>
    <Head :title="member.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div
                        v-if="member.photo"
                        class="h-20 w-20 rounded-full overflow-hidden"
                    >
                        <img
                            :src="`/storage/${member.photo}`"
                            :alt="member.name"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div
                        v-else
                        class="h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center"
                    >
                        <span class="text-2xl font-medium text-primary">
                            {{ member.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ member.name }}</h1>
                        <p class="text-sm text-muted-foreground mt-1">
                            Member ID: <code class="bg-sidebar px-2 py-1 rounded text-xs">{{ member.member_id }}</code>
                        </p>
                        <div class="mt-2">
                            <span :class="getStatusClass(member.status)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                {{ member.status }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a
                        :href="`/members/${member.id}/qr-code`"
                        class="inline-flex items-center justify-center rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        View QR Code
                    </a>
                    <a
                        :href="`/members/${member.id}/edit`"
                        class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90"
                    >
                        Edit Member
                    </a>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Personal Information -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <h2 class="text-lg font-semibold mb-4">Personal Information</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-muted-foreground">Email</dt>
                            <dd class="mt-1 text-sm">{{ member.email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-muted-foreground">Phone</dt>
                            <dd class="mt-1 text-sm">{{ member.phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-muted-foreground">Date of Birth</dt>
                            <dd class="mt-1 text-sm">{{ new Date(member.date_of_birth).toLocaleDateString() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-muted-foreground">Gender</dt>
                            <dd class="mt-1 text-sm capitalize">{{ member.gender }}</dd>
                        </div>
                        <div v-if="member.address">
                            <dt class="text-sm font-medium text-muted-foreground">Address</dt>
                            <dd class="mt-1 text-sm">{{ member.address }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-muted-foreground">Member Since</dt>
                            <dd class="mt-1 text-sm">{{ new Date(member.created_at).toLocaleDateString() }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Emergency Contact -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <h2 class="text-lg font-semibold mb-4">Emergency Contact</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-muted-foreground">Contact Name</dt>
                            <dd class="mt-1 text-sm">{{ member.emergency_contact_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-muted-foreground">Contact Phone</dt>
                            <dd class="mt-1 text-sm">{{ member.emergency_contact_phone }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Subscriptions -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h2 class="text-lg font-semibold mb-4">Subscriptions</h2>
                <div v-if="member.subscriptions.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-sidebar-border">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium">Plan</th>
                                <th class="px-4 py-2 text-left font-medium">Start Date</th>
                                <th class="px-4 py-2 text-left font-medium">End Date</th>
                                <th class="px-4 py-2 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/50">
                            <tr v-for="subscription in member.subscriptions" :key="subscription.id">
                                <td class="px-4 py-3">{{ subscription.membership_plan.name }}</td>
                                <td class="px-4 py-3">{{ new Date(subscription.start_date).toLocaleDateString() }}</td>
                                <td class="px-4 py-3">{{ new Date(subscription.end_date).toLocaleDateString() }}</td>
                                <td class="px-4 py-3">
                                    <span :class="getStatusClass(subscription.status)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                        {{ subscription.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-muted-foreground">No subscriptions found</p>
            </div>

            <!-- Recent Payments -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h2 class="text-lg font-semibold mb-4">Recent Payments</h2>
                <div v-if="member.payments.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-sidebar-border">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium">Amount</th>
                                <th class="px-4 py-2 text-left font-medium">Method</th>
                                <th class="px-4 py-2 text-left font-medium">Status</th>
                                <th class="px-4 py-2 text-left font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/50">
                            <tr v-for="payment in member.payments" :key="payment.id">
                                <td class="px-4 py-3 font-medium">{{ formatCurrency(payment.amount, payment.currency) }}</td>
                                <td class="px-4 py-3 capitalize">{{ payment.payment_method.replace('_', ' ') }}</td>
                                <td class="px-4 py-3">
                                    <span :class="getStatusClass(payment.status)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                        {{ payment.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ new Date(payment.payment_date).toLocaleDateString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-muted-foreground">No payments found</p>
            </div>

            <!-- Recent Attendance -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h2 class="text-lg font-semibold mb-4">Recent Attendance</h2>
                <div v-if="member.attendances.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-sidebar-border">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium">Check In</th>
                                <th class="px-4 py-2 text-left font-medium">Check Out</th>
                                <th class="px-4 py-2 text-left font-medium">Method</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/50">
                            <tr v-for="attendance in member.attendances" :key="attendance.id">
                                <td class="px-4 py-3">{{ new Date(attendance.check_in_time).toLocaleString() }}</td>
                                <td class="px-4 py-3">
                                    {{ attendance.check_out_time ? new Date(attendance.check_out_time).toLocaleString() : 'Still checked in' }}
                                </td>
                                <td class="px-4 py-3 capitalize">{{ attendance.check_in_method.replace('_', ' ') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-muted-foreground">No attendance records found</p>
            </div>
        </div>
    </AppLayout>
</template>
