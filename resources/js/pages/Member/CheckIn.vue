<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LogIn, LogOut, Clock, Calendar, ArrowLeft, CheckCircle } from 'lucide-vue-next';

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
        <div class="border-b border-sidebar-border/70 dark:border-sidebar-border bg-card/80 backdrop-blur-md">
            <div class="container mx-auto px-4 py-6">
                <div class="flex items-center justify-between">
                    <Button as-child variant="ghost" size="sm">
                        <Link href="/member/dashboard" class="flex items-center gap-2">
                            <ArrowLeft class="h-4 w-4" />
                            Back to Dashboard
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8 max-w-2xl">
            <div class="grid gap-6">
                <!-- Main Check-In Button -->
                <Card :class="activeCheckIn ? 'border-red-200 dark:border-red-800' : 'border-primary/20'">
                    <CardContent class="p-8">
                        <div class="text-center">
                            <div class="flex h-24 w-24 items-center justify-center rounded-full mx-auto mb-6"
                                 :class="activeCheckIn
                                     ? 'bg-gradient-to-br from-red-500 to-red-600 shadow-lg shadow-red-500/50'
                                     : 'bg-gradient-to-br from-primary to-primary/80 shadow-lg shadow-primary/50'">
                                <component :is="activeCheckIn ? LogOut : LogIn" class="h-12 w-12 text-white" />
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

                            <div v-if="activeCheckIn" class="mb-8 flex items-center justify-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                                <div class="text-left">
                                    <div class="text-sm font-medium text-green-800 dark:text-green-400">Currently Checked In</div>
                                    <div class="flex items-center gap-1 text-xs text-green-700 dark:text-green-500 mt-1">
                                        <Clock class="h-3 w-3" />
                                        Since {{ new Date(activeCheckIn.check_in_time).toLocaleTimeString() }}
                                    </div>
                                </div>
                            </div>

                            <Button
                                @click="handleCheckIn"
                                :disabled="checkInForm.processing"
                                size="lg"
                                :variant="activeCheckIn ? 'destructive' : 'default'"
                                class="w-full h-16 text-xl"
                            >
                                <component :is="activeCheckIn ? LogOut : LogIn" class="h-6 w-6 mr-2" />
                                <span v-if="checkInForm.processing">Processing...</span>
                                <span v-else>{{ activeCheckIn ? 'Check Out' : 'Check In' }}</span>
                            </Button>

                            <p class="text-xs text-muted-foreground mt-4">
                                One tap to {{ activeCheckIn ? 'check out' : 'check in' }}
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
                        <div v-if="recentAttendances && recentAttendances.length > 0" class="space-y-3">
                            <div
                                v-for="attendance in recentAttendances"
                                :key="attendance.id"
                                class="flex items-center justify-between p-4 bg-muted/50 rounded-lg"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                        <Calendar class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ new Date(attendance.check_in_time).toLocaleDateString() }}</div>
                                        <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                            <Clock class="h-3 w-3" />
                                            {{ new Date(attendance.check_in_time).toLocaleTimeString() }}
                                            <span v-if="attendance.check_out_time">
                                                - {{ new Date(attendance.check_out_time).toLocaleTimeString() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="attendance.duration" class="flex items-center gap-1 text-sm font-medium text-muted-foreground">
                                    <Clock class="h-4 w-4" />
                                    {{ attendance.duration }} min
                                </div>
                                <Badge v-else variant="outline" class="text-green-600 border-green-600">
                                    Active
                                </Badge>
                            </div>
                        </div>

                        <div v-else class="flex flex-col items-center text-center py-8 text-muted-foreground">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-muted mb-4">
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
                        <CardDescription>Simple check-in process</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul class="text-sm text-muted-foreground space-y-3">
                            <li class="flex items-start gap-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-primary">1</span>
                                </div>
                                <span>Tap "Check In" when you arrive at the gym</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-primary">2</span>
                                </div>
                                <span>The system will record your arrival time</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-primary">3</span>
                                </div>
                                <span>When you're done, tap "Check Out" to complete your session</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 flex-shrink-0 mt-0.5">
                                    <span class="text-xs font-semibold text-primary">4</span>
                                </div>
                                <span>Your workout duration will be automatically calculated</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
