<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    error: string | null;
    pin: string | null;
    member: any;
    memberId?: string;
    loginUrl?: string;
}>();
</script>

<template>
    <Head title="Your PIN" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-primary/10 to-accent/10 p-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold tracking-tight">Your Gym PIN</h1>
                <p class="mt-2 text-muted-foreground">Welcome to the Member Portal</p>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-8 shadow-lg">
                <!-- Error State -->
                <div v-if="error" class="text-center">
                    <div class="text-6xl mb-4">⚠️</div>
                    <h2 class="text-xl font-semibold text-red-600 dark:text-red-400 mb-2">Link Issue</h2>
                    <p class="text-muted-foreground mb-6">{{ error }}</p>
                    <a href="/" class="inline-block text-primary hover:underline">
                        Back to Home
                    </a>
                </div>

                <!-- Success State - Show PIN -->
                <div v-else-if="pin && member" class="text-center">
                    <div class="text-5xl mb-4">🎉</div>
                    <h2 class="text-xl font-semibold mb-2">Welcome, {{ member.name }}!</h2>
                    <p class="text-sm text-muted-foreground mb-6">
                        Your account has been created successfully
                    </p>

                    <!-- Member ID -->
                    <div class="mb-6 p-4 bg-sidebar/30 rounded-lg">
                        <div class="text-xs text-muted-foreground mb-1">Member ID</div>
                        <div class="text-lg font-mono font-semibold">{{ memberId }}</div>
                    </div>

                    <!-- PIN Display -->
                    <div class="mb-8 p-6 bg-primary/10 dark:bg-primary/20 rounded-lg border-2 border-primary/30">
                        <div class="text-sm text-muted-foreground mb-2">Your 4-Digit PIN</div>
                        <div class="text-6xl font-bold text-primary tracking-widest font-mono">
                            {{ pin }}
                        </div>
                    </div>

                    <!-- Important Notice -->
                    <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <div class="text-sm font-medium text-yellow-800 dark:text-yellow-400 mb-2">
                            ⚠️ Important
                        </div>
                        <p class="text-xs text-yellow-700 dark:text-yellow-500">
                            Please save this PIN securely. You will need both your Member ID and PIN to access the member portal.
                        </p>
                    </div>

                    <!-- Login Button -->
                    <a :href="loginUrl">
                        <Button class="w-full h-12 text-base">
                            Go to Member Portal
                        </Button>
                    </a>

                    <!-- Instructions -->
                    <div class="mt-8 pt-6 border-t border-sidebar-border/50">
                        <h3 class="text-sm font-semibold mb-3">How to Check In</h3>
                        <ol class="text-xs text-muted-foreground space-y-2 text-left">
                            <li class="flex items-start gap-2">
                                <span class="text-primary font-semibold">1.</span>
                                <span>Visit the member portal using the button above</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-primary font-semibold">2.</span>
                                <span>Enter your Member ID and PIN to login</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-primary font-semibold">3.</span>
                                <span>Tap "Check In" when you arrive at the gym</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-primary font-semibold">4.</span>
                                <span>Tap "Check Out" when you're done with your workout</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-center text-sm text-muted-foreground">
                <p>Need help? Contact our staff at the front desk.</p>
            </div>
        </div>
    </div>
</template>
