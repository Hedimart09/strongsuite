<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Home, Mail, RefreshCcw, XCircle } from 'lucide-vue-next';

interface Props {
    error?: string;
}

defineProps<Props>();
</script>

<template>
    <Head title="Payment Failed" />

    <div
        class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-gray-900 dark:to-gray-800"
    >
        <div
            class="container mx-auto flex min-h-screen items-center justify-center px-4 py-16"
        >
            <div class="w-full max-w-2xl">
                <!-- Error Icon -->
                <div class="mb-8 text-center">
                    <div
                        class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-red-100 dark:bg-red-900"
                    >
                        <XCircle
                            class="h-12 w-12 text-red-600 dark:text-red-400"
                        />
                    </div>
                    <h1
                        class="mb-2 text-3xl font-bold text-gray-900 dark:text-white"
                    >
                        Payment Failed
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        We couldn't process your payment. Please try again.
                    </p>
                </div>

                <!-- Error Details Card -->
                <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-800">
                    <h2
                        class="mb-4 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        What happened?
                    </h2>

                    <div
                        v-if="error"
                        class="mb-6 rounded-lg bg-red-50 p-4 dark:bg-red-900/20"
                    >
                        <p class="text-sm text-red-800 dark:text-red-200">
                            {{ error }}
                        </p>
                    </div>

                    <div v-else class="mb-6">
                        <p class="text-gray-600 dark:text-gray-400">
                            Your payment was not completed. This could be due
                            to:
                        </p>
                        <ul
                            class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-1 h-1.5 w-1.5 rounded-full bg-gray-400"
                                ></span>
                                <span>Insufficient funds in your account</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-1 h-1.5 w-1.5 rounded-full bg-gray-400"
                                ></span>
                                <span>Payment was cancelled</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-1 h-1.5 w-1.5 rounded-full bg-gray-400"
                                ></span>
                                <span
                                    >Technical issue with the payment
                                    gateway</span
                                >
                            </li>
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-1 h-1.5 w-1.5 rounded-full bg-gray-400"
                                ></span>
                                <span>Incorrect payment details</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Support Notice -->
                    <div
                        class="mb-6 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20"
                    >
                        <div class="flex items-start gap-3">
                            <Mail
                                class="mt-0.5 h-5 w-5 text-blue-600 dark:text-blue-400"
                            />
                            <div>
                                <p
                                    class="text-sm font-medium text-blue-900 dark:text-blue-100"
                                >
                                    Need Help?
                                </p>
                                <p
                                    class="text-sm text-blue-700 dark:text-blue-300"
                                >
                                    If you continue to experience issues, please
                                    contact our support team. They'll be happy
                                    to assist you.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button
                            @click="
                                $inertia.visit(
                                    window.history.state?.back ||
                                        '/member/login',
                                )
                            "
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-gray-900 px-6 py-3 text-center font-medium text-white transition-colors hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                        >
                            <RefreshCcw class="h-5 w-5" />
                            Try Again
                        </button>
                        <a
                            href="/member/login"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-300 px-6 py-3 text-center font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <Home class="h-5 w-5" />
                            Member Portal
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <p
                    class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400"
                >
                    Your account has not been charged. You can safely try the
                    payment again.
                </p>
            </div>
        </div>
    </div>
</template>
