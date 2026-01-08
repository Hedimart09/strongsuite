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

    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-primary/10 to-accent/10 p-4"
    >
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold tracking-tight">Your Gym PIN</h1>
                <p class="mt-2 text-muted-foreground">
                    Welcome to the Member Portal
                </p>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 bg-card p-8 shadow-lg dark:border-sidebar-border"
            >
                <!-- Error State -->
                <div v-if="error" class="text-center">
                    <div class="mb-4 text-6xl">⚠️</div>
                    <h2
                        class="mb-2 text-xl font-semibold text-red-600 dark:text-red-400"
                    >
                        Link Issue
                    </h2>
                    <p class="mb-6 text-muted-foreground">{{ error }}</p>
                    <a
                        href="/"
                        class="inline-block text-primary hover:underline"
                    >
                        Back to Home
                    </a>
                </div>

                <!-- Success State - Show PIN -->
                <div v-else-if="pin && member" class="text-center">
                    <div class="mb-4 text-5xl">🎉</div>
                    <h2 class="mb-2 text-xl font-semibold">
                        Welcome, {{ member.name }}!
                    </h2>
                    <p class="mb-6 text-sm text-muted-foreground">
                        Your account has been created successfully
                    </p>

                    <!-- Member ID -->
                    <div class="mb-6 rounded-lg bg-sidebar/30 p-4">
                        <div class="mb-1 text-xs text-muted-foreground">
                            Member ID
                        </div>
                        <div class="font-mono text-lg font-semibold">
                            {{ memberId }}
                        </div>
                    </div>

                    <!-- PIN Display -->
                    <div
                        class="mb-8 rounded-lg border-2 border-primary/30 bg-primary/10 p-6 dark:bg-primary/20"
                    >
                        <div class="mb-2 text-sm text-muted-foreground">
                            Your 4-Digit PIN
                        </div>
                        <div
                            class="font-mono text-6xl font-bold tracking-widest text-primary"
                        >
                            {{ pin }}
                        </div>
                    </div>

                    <!-- Important Notice -->
                    <div
                        class="mb-6 rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20"
                    >
                        <div
                            class="mb-2 text-sm font-medium text-yellow-800 dark:text-yellow-400"
                        >
                            ⚠️ Important
                        </div>
                        <p class="text-xs text-yellow-700 dark:text-yellow-500">
                            Please save this PIN securely. You will need both
                            your Member ID and PIN to access the member portal.
                        </p>
                    </div>

                    <!-- Login Button -->
                    <a :href="loginUrl">
                        <Button class="h-12 w-full text-base">
                            Go to Member Portal
                        </Button>
                    </a>

                    <!-- Instructions -->
                    <div class="mt-8 border-t border-sidebar-border/50 pt-6">
                        <h3 class="mb-3 text-sm font-semibold">
                            How to Check In
                        </h3>
                        <ol
                            class="space-y-2 text-left text-xs text-muted-foreground"
                        >
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-primary"
                                    >1.</span
                                >
                                <span
                                    >Visit the member portal using the button
                                    above</span
                                >
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-primary"
                                    >2.</span
                                >
                                <span
                                    >Enter your Member ID and PIN to login</span
                                >
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-primary"
                                    >3.</span
                                >
                                <span
                                    >Tap "Check In" when you arrive at the
                                    gym</span
                                >
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-primary"
                                    >4.</span
                                >
                                <span
                                    >Tap "Check Out" when you're done with your
                                    workout</span
                                >
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
