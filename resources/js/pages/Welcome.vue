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

    <div class="min-h-screen bg-black">
        <!-- Hero Section with Background -->
        <section class="relative min-h-screen overflow-hidden">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img
                    src="/images/banner4.jpg"
                    alt="Gym Background"
                    class="h-full w-full object-cover"
                    fetchpriority="high"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/70 to-black/80"
                ></div>
            </div>

            <!-- Navigation -->
            <nav
                class="relative z-10 border-b border-white/10 backdrop-blur-sm"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-20 items-center justify-between">
                        <!-- Logo -->
                        <div class="flex items-center">
                            <img
                                src="/images/logo/strongsuite_white.png"
                                alt="StrongSuite"
                                class="h-10 object-contain"
                            />
                        </div>

                        <!-- Navigation Links -->
                        <div class="flex items-center gap-4">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="dashboard()"
                                class="rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                            >
                                Dashboard
                            </Link>
                            <template v-else>
                                <Link
                                    :href="login()"
                                    class="rounded-lg px-6 py-2.5 text-sm font-semibold text-white/90 transition hover:bg-white/10"
                                >
                                    Log in
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="register()"
                                    class="rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                                >
                                    Get Started
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero Content -->
            <div
                class="relative z-10 mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-32"
            >
                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-8">
                    <!-- Hero Text -->
                    <div class="flex flex-col justify-center">
                        <h1
                            class="text-5xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl"
                        >
                            Transform Your
                            <span
                                class="block bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent"
                            >
                                Fitness Business
                            </span>
                        </h1>
                        <p class="mt-6 text-xl leading-8 text-gray-300">
                            Join Strongsuite and experience world-class gym
                            management. From personalized training to
                            state-of-the-art equipment, we're here to help you
                            achieve your fitness goals.
                        </p>
                        <div class="mt-10 flex items-center gap-4">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="register()"
                                class="rounded-lg bg-primary px-8 py-4 text-base font-semibold text-primary-foreground shadow-lg shadow-primary/50 transition hover:bg-primary/90"
                            >
                                Join Now
                            </Link>
                            <a
                                href="#features"
                                class="rounded-lg border-2 border-white/30 px-8 py-4 text-base font-semibold text-white backdrop-blur-sm transition hover:border-white/50 hover:bg-white/10"
                            >
                                Learn More
                            </a>
                        </div>
                    </div>

                    <!-- Hero Stats -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            class="group rounded-2xl border border-primary/30 bg-gradient-to-br from-primary/10 to-primary/5 p-8 backdrop-blur-sm transition hover:border-primary/50 hover:from-primary/20 hover:to-primary/10"
                        >
                            <div class="text-4xl font-bold text-primary">
                                500+
                            </div>
                            <div class="mt-2 text-sm font-medium text-white/90">
                                Active Members
                            </div>
                        </div>
                        <div
                            class="group rounded-2xl border border-primary/30 bg-gradient-to-br from-primary/10 to-primary/5 p-8 backdrop-blur-sm transition hover:border-primary/50 hover:from-primary/20 hover:to-primary/10"
                        >
                            <div class="text-4xl font-bold text-primary">
                                50+
                            </div>
                            <div class="mt-2 text-sm font-medium text-white/90">
                                Expert Trainers
                            </div>
                        </div>
                        <div
                            class="group rounded-2xl border border-primary/30 bg-gradient-to-br from-primary/10 to-primary/5 p-8 backdrop-blur-sm transition hover:border-primary/50 hover:from-primary/20 hover:to-primary/10"
                        >
                            <div class="text-4xl font-bold text-primary">
                                24/7
                            </div>
                            <div class="mt-2 text-sm font-medium text-white/90">
                                Access Available
                            </div>
                        </div>
                        <div
                            class="group rounded-2xl border border-primary/30 bg-gradient-to-br from-primary/10 to-primary/5 p-8 backdrop-blur-sm transition hover:border-primary/50 hover:from-primary/20 hover:to-primary/10"
                        >
                            <div class="text-4xl font-bold text-primary">
                                5★
                            </div>
                            <div class="mt-2 text-sm font-medium text-white/90">
                                Member Ratings
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="bg-gray-950 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2
                        class="text-3xl font-bold tracking-tight text-white sm:text-4xl"
                    >
                        Everything You Need
                    </h2>
                    <p class="mt-4 text-lg text-gray-400">
                        Comprehensive gym management tools designed for your
                        success
                    </p>
                </div>

                <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div
                        class="group rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm transition hover:bg-white/10"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/20"
                        >
                            <svg
                                class="h-6 w-6 text-primary"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-white">
                            Member Management
                        </h3>
                        <p class="mt-2 text-gray-400">
                            Effortlessly manage member profiles, subscriptions,
                            and attendance with QR code check-ins.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div
                        class="group rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm transition hover:bg-white/10"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/20"
                        >
                            <svg
                                class="h-6 w-6 text-primary"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-white">
                            Flexible Payments
                        </h3>
                        <p class="mt-2 text-gray-400">
                            Accept payments via Stripe, Paystack, Flutterwave,
                            and mobile money. Multi-currency support included.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div
                        class="group rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm transition hover:bg-white/10"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/20"
                        >
                            <svg
                                class="h-6 w-6 text-primary"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-white">
                            Class Scheduling
                        </h3>
                        <p class="mt-2 text-gray-400">
                            Schedule classes, manage bookings, and handle
                            waitlists with an intuitive booking system.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div
                        class="group rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm transition hover:bg-white/10"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/20"
                        >
                            <svg
                                class="h-6 w-6 text-primary"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-white">
                            Analytics & Reports
                        </h3>
                        <p class="mt-2 text-gray-400">
                            Track member growth, revenue, attendance trends, and
                            gain insights to grow your business.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div
                        class="group rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm transition hover:bg-white/10"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/20"
                        >
                            <svg
                                class="h-6 w-6 text-primary"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-white">
                            Mobile Friendly
                        </h3>
                        <p class="mt-2 text-gray-400">
                            Access your gym management system anywhere, anytime
                            with our responsive mobile interface.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div
                        class="group rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur-sm transition hover:bg-white/10"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/20"
                        >
                            <svg
                                class="h-6 w-6 text-primary"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-white">
                            Global Ready
                        </h3>
                        <p class="mt-2 text-gray-400">
                            Multi-currency, multi-language, and timezone
                            support. Built for Ghana and the world.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="bg-gray-900 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2
                        class="text-3xl font-bold tracking-tight text-white sm:text-4xl"
                    >
                        Our Facility
                    </h2>
                    <p class="mt-4 text-lg text-gray-400">
                        State-of-the-art equipment and modern training spaces
                    </p>
                </div>

                <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Gallery Image 1 -->
                    <div
                        class="group relative aspect-[4/3] overflow-hidden rounded-2xl"
                    >
                        <img
                            src="/images/banner2.jpg"
                            alt="Gym Equipment"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                            loading="lazy"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"
                        ></div>
                    </div>

                    <!-- Gallery Image 2 -->
                    <div
                        class="group relative aspect-[4/3] overflow-hidden rounded-2xl"
                    >
                        <img
                            src="/images/banner3.jpg"
                            alt="Training Area"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                            loading="lazy"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"
                        ></div>
                    </div>

                    <!-- Gallery Image 3 -->
                    <div
                        class="group relative aspect-[4/3] overflow-hidden rounded-2xl"
                    >
                        <img
                            src="/images/banner4.jpg"
                            alt="Fitness Studio"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                            loading="lazy"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"
                        ></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative overflow-hidden py-24">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img
                    src="/images/banner2.jpg"
                    alt="CTA Background"
                    class="h-full w-full object-cover"
                    loading="lazy"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-br from-black/90 via-black/80 to-primary/60"
                ></div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2
                        class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl"
                    >
                        Ready to Get Started?
                    </h2>
                    <p class="mt-6 text-xl text-gray-300">
                        Join hundreds of members who trust Strongsuite for their
                        fitness journey
                    </p>
                    <div
                        class="mt-10 flex flex-col justify-center gap-4 sm:flex-row"
                    >
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="register()"
                            class="rounded-lg bg-primary px-8 py-4 text-base font-semibold text-primary-foreground shadow-xl shadow-primary/30 transition hover:bg-primary/90"
                        >
                            Join Now
                        </Link>
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="login()"
                            class="rounded-lg border-2 border-white/30 px-8 py-4 text-base font-semibold text-white backdrop-blur-sm transition hover:border-white/50 hover:bg-white/10"
                        >
                            Sign In
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-white/10 bg-gray-950">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <!-- Company Info -->
                    <div class="md:col-span-2">
                        <img
                            src="/images/logo/strongsuite_white.png"
                            alt="StrongSuite"
                            class="h-8 object-contain"
                        />
                        <p class="mt-4 text-sm text-gray-400">
                            Modern gym management system built for Ghana and the
                            world. Manage members, payments, classes, and more
                            with ease.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h4 class="text-sm font-semibold text-white">
                            Quick Links
                        </h4>
                        <ul class="mt-4 space-y-2">
                            <li>
                                <a
                                    href="#features"
                                    class="text-sm text-gray-400 transition hover:text-white"
                                >
                                    Features
                                </a>
                            </li>
                            <li>
                                <Link
                                    :href="login()"
                                    class="text-sm text-gray-400 transition hover:text-white"
                                >
                                    Login
                                </Link>
                            </li>
                            <li v-if="canRegister">
                                <Link
                                    :href="register()"
                                    class="text-sm text-gray-400 transition hover:text-white"
                                >
                                    Register
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h4 class="text-sm font-semibold text-white">
                            Contact
                        </h4>
                        <ul class="mt-4 space-y-2">
                            <li class="text-sm text-gray-400">
                                Email: info@strongsuite.com
                            </li>
                            <li class="text-sm text-gray-400">
                                Phone: +233 XX XXX XXXX
                            </li>
                            <li class="text-sm text-gray-400">Accra, Ghana</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 border-t border-white/10 pt-8">
                    <p class="text-center text-sm text-gray-400">
                        © {{ new Date().getFullYear() }} Strongsuite. All
                        rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
