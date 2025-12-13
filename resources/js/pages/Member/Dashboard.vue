<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    member: any;
    activeSubscription: any;
    activeCheckIn: any;
}>();

const logoutForm = useForm({});

const logout = () => {
    logoutForm.post('/member/logout');
};

const subscriptionDaysLeft = computed(() => {
    if (!props.activeSubscription) return 0;
    const endDate = new Date(props.activeSubscription.end_date);
    const today = new Date();
    const diff = endDate.getTime() - today.getTime();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
});

const subscriptionStatus = computed(() => {
    if (!props.activeSubscription) return 'inactive';
    if (subscriptionDaysLeft.value <= 0) return 'expired';
    if (subscriptionDaysLeft.value <= 7) return 'expiring';
    return 'active';
});
</script>

<template>
    <Head title="Member Dashboard" />

    <div class="min-h-screen bg-gradient-to-br from-primary/10 to-accent/10">
        <!-- Header -->
        <div class="border-b border-sidebar-border/70 dark:border-sidebar-border bg-card/50 backdrop-blur-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold">Welcome, {{ member.name }}!</h1>
                        <p class="text-sm text-muted-foreground">{{ member.member_id }}</p>
                    </div>
                    <Button variant="ghost" size="sm" @click="logout" :disabled="logoutForm.processing">
                        Logout
                    </Button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-6 max-w-4xl">
            <div class="grid gap-6">
                <!-- Quick Check-In Button -->
                <Link href="/member/check-in" class="block">
                    <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-primary p-8 text-center shadow-lg hover:shadow-xl transition-shadow cursor-pointer">
                        <div class="text-white">
                            <div class="text-6xl mb-4">
                                {{ activeCheckIn ? '👋' : '✓' }}
                            </div>
                            <h2 class="text-2xl font-bold mb-2">
                                {{ activeCheckIn ? 'Check Out' : 'Check In' }}
                            </h2>
                            <p class="text-primary-foreground/80">
                                {{ activeCheckIn ? 'Tap to complete your workout' : 'Tap to start your workout' }}
                            </p>
                            <div v-if="activeCheckIn" class="mt-4 text-sm text-primary-foreground/70">
                                Checked in at {{ new Date(activeCheckIn.check_in_time).toLocaleTimeString() }}
                            </div>
                        </div>
                    </div>
                </Link>

                <!-- Subscription Status -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-6 shadow-lg">
                    <h3 class="text-lg font-semibold mb-4">Membership Status</h3>

                    <div v-if="activeSubscription">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="text-2xl font-bold">{{ activeSubscription.membership_plan.name }}</div>
                                <div class="text-sm text-muted-foreground">Active Membership</div>
                            </div>
                            <div
                                class="px-3 py-1 rounded-full text-sm font-medium"
                                :class="{
                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': subscriptionStatus === 'active',
                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': subscriptionStatus === 'expiring',
                                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': subscriptionStatus === 'expired'
                                }"
                            >
                                {{ subscriptionDaysLeft }} days left
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-muted-foreground">Start Date</div>
                                <div class="font-medium">{{ new Date(activeSubscription.start_date).toLocaleDateString() }}</div>
                            </div>
                            <div>
                                <div class="text-muted-foreground">End Date</div>
                                <div class="font-medium">{{ new Date(activeSubscription.end_date).toLocaleDateString() }}</div>
                            </div>
                        </div>

                        <div v-if="subscriptionDaysLeft <= 7 && subscriptionDaysLeft > 0" class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <p class="text-sm text-yellow-800 dark:text-yellow-400">
                                Your membership is expiring soon. Please contact the gym to renew.
                            </p>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <div class="text-4xl mb-2">⚠️</div>
                        <div class="text-lg font-medium text-red-600 dark:text-red-400 mb-2">No Active Membership</div>
                        <p class="text-sm text-muted-foreground">Please contact the gym to activate your membership.</p>
                    </div>
                </div>

                <!-- Recent Attendance -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-6 shadow-lg">
                    <h3 class="text-lg font-semibold mb-4">Recent Visits</h3>

                    <div v-if="member.attendances && member.attendances.length > 0" class="space-y-3">
                        <div
                            v-for="attendance in member.attendances"
                            :key="attendance.id"
                            class="flex items-center justify-between p-3 bg-sidebar/30 rounded-lg"
                        >
                            <div>
                                <div class="font-medium">{{ new Date(attendance.check_in_time).toLocaleDateString() }}</div>
                                <div class="text-sm text-muted-foreground">
                                    {{ new Date(attendance.check_in_time).toLocaleTimeString() }}
                                    <span v-if="attendance.check_out_time">
                                        - {{ new Date(attendance.check_out_time).toLocaleTimeString() }}
                                    </span>
                                    <span v-else class="text-green-600 dark:text-green-400 font-medium"> (Active)</span>
                                </div>
                            </div>
                            <div v-if="attendance.duration" class="text-sm font-medium text-muted-foreground">
                                {{ attendance.duration }} min
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8 text-muted-foreground">
                        <div class="text-4xl mb-2">📅</div>
                        <p class="text-sm">No recent visits</p>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-6 shadow-lg">
                    <h3 class="text-lg font-semibold mb-4">Need Help?</h3>
                    <p class="text-sm text-muted-foreground">
                        If you have any questions or need assistance, please contact our staff at the front desk.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
