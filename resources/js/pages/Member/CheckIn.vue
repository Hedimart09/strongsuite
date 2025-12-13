<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, Link, useForm } from '@inertiajs/vue3';

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
        <div class="border-b border-sidebar-border/70 dark:border-sidebar-border bg-card/50 backdrop-blur-sm">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <Link href="/member/dashboard" class="text-sm text-primary hover:underline">
                        ← Back to Dashboard
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8 max-w-2xl">
            <div class="grid gap-6">
                <!-- Main Check-In Button -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-8 shadow-lg">
                    <div class="text-center">
                        <div class="text-8xl mb-6">
                            {{ activeCheckIn ? '👋' : '✓' }}
                        </div>

                        <h1 class="text-3xl font-bold mb-4">
                            {{ activeCheckIn ? 'Ready to Leave?' : 'Ready to Workout?' }}
                        </h1>

                        <p class="text-muted-foreground mb-8">
                            {{ activeCheckIn
                                ? 'Tap the button below to check out and complete your session'
                                : 'Tap the button below to check in and start your workout'
                            }}
                        </p>

                        <div v-if="activeCheckIn" class="mb-8 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <div class="text-sm font-medium text-green-800 dark:text-green-400">Currently Checked In</div>
                            <div class="text-xs text-green-700 dark:text-green-500 mt-1">
                                Since {{ new Date(activeCheckIn.check_in_time).toLocaleTimeString() }}
                            </div>
                        </div>

                        <Button
                            @click="handleCheckIn"
                            :disabled="checkInForm.processing"
                            size="lg"
                            :class="activeCheckIn ? 'bg-red-600 hover:bg-red-700' : ''"
                            class="w-full h-16 text-xl"
                        >
                            <span v-if="checkInForm.processing">Processing...</span>
                            <span v-else>{{ activeCheckIn ? 'Check Out' : 'Check In' }}</span>
                        </Button>

                        <p class="text-xs text-muted-foreground mt-4">
                            One tap to {{ activeCheckIn ? 'check out' : 'check in' }}
                        </p>
                    </div>
                </div>

                <!-- Recent Check-Ins -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-6 shadow-lg">
                    <h3 class="text-lg font-semibold mb-4">Recent Check-Ins</h3>

                    <div v-if="recentAttendances && recentAttendances.length > 0" class="space-y-3">
                        <div
                            v-for="attendance in recentAttendances"
                            :key="attendance.id"
                            class="flex items-center justify-between p-3 bg-sidebar/30 rounded-lg text-sm"
                        >
                            <div>
                                <div class="font-medium">{{ new Date(attendance.check_in_time).toLocaleDateString() }}</div>
                                <div class="text-xs text-muted-foreground">
                                    {{ new Date(attendance.check_in_time).toLocaleTimeString() }}
                                    <span v-if="attendance.check_out_time">
                                        - {{ new Date(attendance.check_out_time).toLocaleTimeString() }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="attendance.duration" class="text-xs font-medium text-muted-foreground">
                                {{ attendance.duration }} min
                            </div>
                            <div v-else class="text-xs font-medium text-green-600 dark:text-green-400">
                                Active
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8 text-muted-foreground">
                        <p class="text-sm">No recent check-ins</p>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-6 shadow-lg">
                    <h3 class="text-sm font-semibold mb-3">How It Works</h3>
                    <ul class="text-sm text-muted-foreground space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="text-primary">•</span>
                            <span>Tap "Check In" when you arrive at the gym</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-primary">•</span>
                            <span>The system will record your arrival time</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-primary">•</span>
                            <span>When you're done, tap "Check Out" to complete your session</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-primary">•</span>
                            <span>Your workout duration will be automatically calculated</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
