<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome to Strongsuite" />

    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Navigation -->
        <nav class="border-b border-gray-200 dark:border-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Strongsuite
                        </h1>
                    </div>

                    <!-- Navigation Links -->
                    <div class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                            >
                                Get Started
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-32">
                <div class="grid gap-12 lg:grid-cols-2 lg:gap-8">
                    <!-- Hero Content -->
                    <div class="flex flex-col justify-center">
                        <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl dark:text-white">
                            Transform Your Fitness Journey
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">
                            Join Strongsuite and experience world-class gym management.
                            From personalized training to state-of-the-art equipment,
                            we're here to help you achieve your fitness goals.
                        </p>
                        <div class="mt-10 flex items-center gap-4">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="register()"
                                class="rounded-lg bg-indigo-600 px-8 py-3 text-base font-medium text-white transition hover:bg-indigo-700"
                            >
                                Join Now
                            </Link>
                            <a
                                href="#features"
                                class="rounded-lg border border-gray-300 px-8 py-3 text-base font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                Learn More
                            </a>
                        </div>
                    </div>

                    <!-- Hero Image Placeholder -->
                    <div class="relative">
                        <div class="aspect-square overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 lg:aspect-[4/3]">
                            <!-- Placeholder for hero image -->
                            <div class="flex h-full items-center justify-center">
                                <div class="text-center text-white/80">
                                    <svg class="mx-auto h-24 w-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="mt-2 text-sm">Hero Image</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="bg-gray-50 py-20 dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                        Everything You Need
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        Comprehensive gym management tools designed for your success
                    </p>
                </div>

                <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="rounded-lg bg-white p-8 shadow-sm dark:bg-gray-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                            Member Management
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Effortlessly manage member profiles, subscriptions, and attendance with QR code check-ins.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="rounded-lg bg-white p-8 shadow-sm dark:bg-gray-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                            Flexible Payments
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Accept payments via Stripe, Paystack, Flutterwave, and mobile money. Multi-currency support included.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="rounded-lg bg-white p-8 shadow-sm dark:bg-gray-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                            Class Scheduling
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Schedule classes, manage bookings, and handle waitlists with an intuitive booking system.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="rounded-lg bg-white p-8 shadow-sm dark:bg-gray-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                            Analytics & Reports
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Track member growth, revenue, attendance trends, and gain insights to grow your business.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="rounded-lg bg-white p-8 shadow-sm dark:bg-gray-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                            Mobile Friendly
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Access your gym management system anywhere, anytime with our responsive mobile interface.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="rounded-lg bg-white p-8 shadow-sm dark:bg-gray-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                            Global Ready
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Multi-currency, multi-language, and timezone support. Built for Ghana and the world.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Section (Image Placeholders) -->
        <section class="py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                        Our Facility
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        State-of-the-art equipment and modern training spaces
                    </p>
                </div>

                <div class="mt-16 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Gallery Image 1 -->
                    <div class="aspect-video overflow-hidden rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500">
                        <div class="flex h-full items-center justify-center">
                            <div class="text-center text-white/80">
                                <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm">Gallery Image 1</p>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery Image 2 -->
                    <div class="aspect-video overflow-hidden rounded-lg bg-gradient-to-br from-purple-500 to-pink-500">
                        <div class="flex h-full items-center justify-center">
                            <div class="text-center text-white/80">
                                <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm">Gallery Image 2</p>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery Image 3 -->
                    <div class="aspect-video overflow-hidden rounded-lg bg-gradient-to-br from-orange-500 to-red-500">
                        <div class="flex h-full items-center justify-center">
                            <div class="text-center text-white/80">
                                <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm">Gallery Image 3</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-indigo-600 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Ready to Get Started?
                    </h2>
                    <p class="mt-4 text-lg text-indigo-100">
                        Join hundreds of members who trust Strongsuite for their fitness journey
                    </p>
                    <div class="mt-8 flex justify-center gap-4">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="register()"
                            class="rounded-lg bg-white px-8 py-3 text-base font-medium text-indigo-600 transition hover:bg-gray-50"
                        >
                            Join Now
                        </Link>
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="login()"
                            class="rounded-lg border border-white px-8 py-3 text-base font-medium text-white transition hover:bg-indigo-700"
                        >
                            Sign In
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <!-- Company Info -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            Strongsuite
                        </h3>
                        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            Modern gym management system built for Ghana and the world.
                            Manage members, payments, classes, and more with ease.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                            Quick Links
                        </h4>
                        <ul class="mt-4 space-y-2">
                            <li>
                                <a href="#features" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Features
                                </a>
                            </li>
                            <li>
                                <Link :href="login()" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Login
                                </Link>
                            </li>
                            <li v-if="canRegister">
                                <Link :href="register()" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Register
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                            Contact
                        </h4>
                        <ul class="mt-4 space-y-2">
                            <li class="text-sm text-gray-600 dark:text-gray-400">
                                Email: info@strongsuite.com
                            </li>
                            <li class="text-sm text-gray-600 dark:text-gray-400">
                                Phone: +233 XX XXX XXXX
                            </li>
                            <li class="text-sm text-gray-600 dark:text-gray-400">
                                Accra, Ghana
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-200 pt-8 dark:border-gray-800">
                    <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                        © {{ new Date().getFullYear() }} Strongsuite. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
