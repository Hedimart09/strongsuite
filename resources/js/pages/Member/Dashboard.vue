<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar,
    CheckCircle,
    Clock,
    IdCard,
    LogIn,
    LogOut,
    User,
} from 'lucide-vue-next';
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
        <div
            class="border-b border-sidebar-border/70 bg-card/80 backdrop-blur-md dark:border-sidebar-border"
        >
            <div class="container mx-auto px-4 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10"
                        >
                            <User class="h-6 w-6 text-primary" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">
                                Welcome, {{ member.name }}!
                            </h1>
                            <div
                                class="flex items-center gap-2 text-sm text-muted-foreground"
                            >
                                <IdCard class="h-4 w-4" />
                                {{ member.member_id }}
                            </div>
                        </div>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="logout"
                        :disabled="logoutForm.processing"
                    >
                        <LogOut class="mr-2 h-4 w-4" />
                        Logout
                    </Button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto max-w-4xl px-4 py-6">
            <div class="grid gap-6">
                <!-- Quick Check-In Button -->
                <Link href="/member/check-in" class="block">
                    <Card
                        class="cursor-pointer overflow-hidden border-2 bg-primary transition-all hover:shadow-xl"
                    >
                        <CardContent class="p-8">
                            <div
                                class="flex flex-col items-center text-center text-white"
                            >
                                <div
                                    class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-white/20"
                                >
                                    <component
                                        :is="activeCheckIn ? LogOut : LogIn"
                                        class="h-10 w-10"
                                    />
                                </div>
                                <h2 class="mb-2 text-3xl font-bold">
                                    {{
                                        activeCheckIn ? 'Check Out' : 'Check In'
                                    }}
                                </h2>
                                <p class="text-lg text-primary-foreground/90">
                                    {{
                                        activeCheckIn
                                            ? 'Tap to complete your workout'
                                            : 'Tap to start your workout'
                                    }}
                                </p>
                                <div
                                    v-if="activeCheckIn"
                                    class="mt-4 flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm text-primary-foreground/80"
                                >
                                    <Clock class="h-4 w-4" />
                                    Checked in at
                                    {{
                                        new Date(
                                            activeCheckIn.check_in_time,
                                        ).toLocaleTimeString()
                                    }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </Link>

                <!-- Subscription Status -->
                <Card>
                    <CardHeader>
                        <CardTitle>Membership Status</CardTitle>
                        <CardDescription
                            >Your current membership plan</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div v-if="activeSubscription">
                            <div class="mb-6 flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold">
                                        {{
                                            activeSubscription.membership_plan
                                                .name
                                        }}
                                    </div>
                                    <div
                                        class="mt-1 flex items-center gap-2 text-sm text-muted-foreground"
                                    >
                                        <CheckCircle
                                            class="h-4 w-4 text-green-600"
                                        />
                                        Active Membership
                                    </div>
                                </div>
                                <Badge
                                    :variant="
                                        subscriptionStatus === 'active'
                                            ? 'default'
                                            : subscriptionStatus === 'expiring'
                                              ? 'outline'
                                              : 'destructive'
                                    "
                                >
                                    {{ subscriptionDaysLeft }} days left
                                </Badge>
                            </div>

                            <div
                                class="grid grid-cols-2 gap-4 rounded-lg bg-muted/50 p-4"
                            >
                                <div class="flex items-center gap-2">
                                    <Calendar
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            Start Date
                                        </div>
                                        <div class="font-medium">
                                            {{
                                                new Date(
                                                    activeSubscription.start_date,
                                                ).toLocaleDateString()
                                            }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Calendar
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            End Date
                                        </div>
                                        <div class="font-medium">
                                            {{
                                                new Date(
                                                    activeSubscription.end_date,
                                                ).toLocaleDateString()
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="
                                    subscriptionDaysLeft <= 7 &&
                                    subscriptionDaysLeft > 0
                                "
                                class="mt-4 flex items-start gap-3 rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20"
                            >
                                <AlertCircle
                                    class="mt-0.5 h-5 w-5 flex-shrink-0 text-yellow-600 dark:text-yellow-500"
                                />
                                <p
                                    class="text-sm text-yellow-800 dark:text-yellow-400"
                                >
                                    Your membership is expiring soon. Please
                                    contact the gym to renew.
                                </p>
                            </div>
                        </div>

                        <div
                            v-else
                            class="flex flex-col items-center py-8 text-center"
                        >
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20"
                            >
                                <AlertCircle
                                    class="h-8 w-8 text-red-600 dark:text-red-400"
                                />
                            </div>
                            <div
                                class="mb-2 text-lg font-semibold text-red-600 dark:text-red-400"
                            >
                                No Active Membership
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Please contact the gym to activate your
                                membership.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Attendance -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Visits</CardTitle>
                        <CardDescription
                            >Your gym attendance history</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="
                                member.attendances &&
                                member.attendances.length > 0
                            "
                            class="space-y-3"
                        >
                            <div
                                v-for="attendance in member.attendances"
                                :key="attendance.id"
                                class="flex items-center justify-between rounded-lg bg-muted/50 p-4"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Calendar
                                            class="h-5 w-5 text-primary"
                                        />
                                    </div>
                                    <div>
                                        <div class="font-medium">
                                            {{
                                                new Date(
                                                    attendance.check_in_time,
                                                ).toLocaleDateString()
                                            }}
                                        </div>
                                        <div
                                            class="flex items-center gap-2 text-sm text-muted-foreground"
                                        >
                                            <Clock class="h-3 w-3" />
                                            {{
                                                new Date(
                                                    attendance.check_in_time,
                                                ).toLocaleTimeString()
                                            }}
                                            <span
                                                v-if="attendance.check_out_time"
                                            >
                                                -
                                                {{
                                                    new Date(
                                                        attendance.check_out_time,
                                                    ).toLocaleTimeString()
                                                }}
                                            </span>
                                            <Badge
                                                v-else
                                                variant="outline"
                                                class="ml-2 border-green-600 text-green-600"
                                            >
                                                Active
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-if="attendance.duration"
                                    class="flex items-center gap-1 text-sm font-medium text-muted-foreground"
                                >
                                    <Clock class="h-4 w-4" />
                                    {{ attendance.duration }} min
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="flex flex-col items-center py-8 text-center text-muted-foreground"
                        >
                            <div
                                class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted"
                            >
                                <Calendar class="h-8 w-8" />
                            </div>
                            <p class="text-sm">No recent visits</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Contact Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Need Help?</CardTitle>
                        <CardDescription>Contact information</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            If you have any questions or need assistance, please
                            contact our staff at the front desk.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
