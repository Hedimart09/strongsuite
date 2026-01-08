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
    ArrowLeft,
    Calendar,
    CheckCircle,
    Clock,
    LogIn,
    LogOut,
} from 'lucide-vue-next';

const props = defineProps<{
    activeCheckIn: any;
    recentAttendances: any[];
}>();

const checkInForm = useForm({});

const handleCheckIn = () => {
    checkInForm.post('/member/check-in', {
        preserveScroll: true,
        onSuccess: () => {
            // Form will be reset automatically
        },
    });
};
</script>

<template>
    <Head title="Check In" />

    <div class="min-h-screen bg-gradient-to-br from-primary/10 to-accent/10">
        <!-- Header -->
        <div
            class="border-b border-sidebar-border/70 bg-card/80 backdrop-blur-md dark:border-sidebar-border"
        >
            <div class="container mx-auto px-4 py-6">
                <div class="flex items-center justify-between">
                    <Button as-child variant="ghost" size="sm">
                        <Link
                            href="/member/dashboard"
                            class="flex items-center gap-2"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Back to Dashboard
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto max-w-2xl px-4 py-8">
            <div class="grid gap-6">
                <!-- Main Check-In Button -->
                <Card
                    :class="
                        activeCheckIn
                            ? 'border-red-200 dark:border-red-800'
                            : 'border-primary/20'
                    "
                >
                    <CardContent class="p-8">
                        <div class="text-center">
                            <div
                                class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full"
                                :class="
                                    activeCheckIn
                                        ? 'bg-gradient-to-br from-red-500 to-red-600 shadow-lg shadow-red-500/50'
                                        : 'bg-gradient-to-br from-primary to-primary/80 shadow-lg shadow-primary/50'
                                "
                            >
                                <component
                                    :is="activeCheckIn ? LogOut : LogIn"
                                    class="h-12 w-12 text-white"
                                />
                            </div>

                            <h1 class="mb-4 text-3xl font-bold">
                                {{
                                    activeCheckIn
                                        ? 'Ready to Leave?'
                                        : 'Ready to Workout?'
                                }}
                            </h1>

                            <p class="mb-8 text-muted-foreground">
                                {{
                                    activeCheckIn
                                        ? 'Tap the button below to check out and complete your session'
                                        : 'Tap the button below to check in and start your workout'
                                }}
                            </p>

                            <div
                                v-if="activeCheckIn"
                                class="mb-8 flex items-center justify-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20"
                            >
                                <CheckCircle
                                    class="h-5 w-5 text-green-600 dark:text-green-400"
                                />
                                <div class="text-left">
                                    <div
                                        class="text-sm font-medium text-green-800 dark:text-green-400"
                                    >
                                        Currently Checked In
                                    </div>
                                    <div
                                        class="mt-1 flex items-center gap-1 text-xs text-green-700 dark:text-green-500"
                                    >
                                        <Clock class="h-3 w-3" />
                                        Since
                                        {{
                                            new Date(
                                                activeCheckIn.check_in_time,
                                            ).toLocaleTimeString()
                                        }}
                                    </div>
                                </div>
                            </div>

                            <Button
                                @click="handleCheckIn"
                                :disabled="checkInForm.processing"
                                size="lg"
                                :variant="
                                    activeCheckIn ? 'destructive' : 'default'
                                "
                                class="h-16 w-full text-xl"
                            >
                                <component
                                    :is="activeCheckIn ? LogOut : LogIn"
                                    class="mr-2 h-6 w-6"
                                />
                                <span v-if="checkInForm.processing"
                                    >Processing...</span
                                >
                                <span v-else>{{
                                    activeCheckIn ? 'Check Out' : 'Check In'
                                }}</span>
                            </Button>

                            <p class="mt-4 text-xs text-muted-foreground">
                                One tap to
                                {{ activeCheckIn ? 'check out' : 'check in' }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Check-Ins -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Check-Ins</CardTitle>
                        <CardDescription>Your workout history</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="
                                recentAttendances &&
                                recentAttendances.length > 0
                            "
                            class="space-y-3"
                        >
                            <div
                                v-for="attendance in recentAttendances"
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
                                            class="flex items-center gap-2 text-xs text-muted-foreground"
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
                                <Badge
                                    v-else
                                    variant="outline"
                                    class="border-green-600 text-green-600"
                                >
                                    Active
                                </Badge>
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
                            <p class="text-sm">No recent check-ins</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Instructions -->
                <Card>
                    <CardHeader>
                        <CardTitle>How It Works</CardTitle>
                        <CardDescription
                            >Simple check-in process</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-3 text-sm text-muted-foreground">
                            <li class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                >
                                    <span
                                        class="text-xs font-semibold text-primary"
                                        >1</span
                                    >
                                </div>
                                <span
                                    >Tap "Check In" when you arrive at the
                                    gym</span
                                >
                            </li>
                            <li class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                >
                                    <span
                                        class="text-xs font-semibold text-primary"
                                        >2</span
                                    >
                                </div>
                                <span
                                    >The system will record your arrival
                                    time</span
                                >
                            </li>
                            <li class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                >
                                    <span
                                        class="text-xs font-semibold text-primary"
                                        >3</span
                                    >
                                </div>
                                <span
                                    >When you're done, tap "Check Out" to
                                    complete your session</span
                                >
                            </li>
                            <li class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-primary/10"
                                >
                                    <span
                                        class="text-xs font-semibold text-primary"
                                        >4</span
                                    >
                                </div>
                                <span
                                    >Your workout duration will be automatically
                                    calculated</span
                                >
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
