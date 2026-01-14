<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { 
    Users, 
    Dumbbell, 
    Calendar, 
    TrendingUp, 
    Smartphone, 
    Globe, 
    CreditCard, 
    Clock, 
    ShieldCheck, 
    CheckCircle, 
    Star,
    ArrowRight,
    Quote,
    Menu,
    X
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const mobileMenuOpen = ref(false);
const pricingInterval = ref<'month' | 'year'>('month');
const stats = ref([
    { value: 0, target: 500, label: 'Active Members', suffix: '+' },
    { value: 0, target: 50, label: 'Expert Trainers', suffix: '+' },
    { value: 0, target: 24, label: 'Access Available', suffix: '/7' },
    { value: 0, target: 5, label: 'Member Ratings', suffix: '★' },
]);

const features = [
    {
        icon: Users,
        title: 'Member Management',
        description: 'Effortlessly manage member profiles, subscriptions, and attendance with QR code check-ins.',
    },
    {
        icon: CreditCard,
        title: 'Flexible Payments',
        description: 'Accept payments via Stripe, Paystack, Flutterwave, and mobile money. Multi-currency support included.',
    },
    {
        icon: Calendar,
        title: 'Class Scheduling',
        description: 'Schedule classes, manage bookings, and handle waitlists with an intuitive booking system.',
    },
    {
        icon: TrendingUp,
        title: 'Analytics & Reports',
        description: 'Track member growth, revenue, attendance trends, and gain insights to grow your business.',
    },
    {
        icon: Smartphone,
        title: 'Mobile Friendly',
        description: 'Access your gym management system anywhere, anytime with our responsive mobile interface.',
    },
    {
        icon: Globe,
        title: 'Global Ready',
        description: 'Multi-currency, multi-language, and timezone support. Built for Ghana and the world.',
    },
];

const steps = [
    {
        number: '01',
        title: 'Sign Up',
        description: 'Create your gym account in under 2 minutes with our simple onboarding process.',
        icon: Users,
    },
    {
        number: '02',
        title: 'Set Up Your Gym',
        description: 'Add your members, staff, and configure your membership plans and class schedules.',
        icon: Dumbbell,
    },
    {
        number: '03',
        title: 'Grow Your Business',
        description: 'Start managing your gym efficiently and watch your business thrive with data-driven insights.',
        icon: TrendingUp,
    },
];

const testimonials = [
    {
        quote: 'Strongsuite transformed how we manage our gym. Member check-ins are instant, and our revenue has increased by 40% in just 3 months!',
        author: 'Sarah Johnson',
        role: 'Owner, FitLife Gym',
        rating: 5,
        featured: true,
        metric: '+40% Revenue',
    },
    {
        quote: 'The payment integration with mobile money made it so easy for our Ghanaian members. No more cash handling!',
        author: 'Kwame Mensah',
        role: 'Manager, PowerZone Fitness',
        rating: 5,
    },
    {
        quote: 'Best investment we made. The attendance tracking and automated billing saved us 20 hours per week.',
        author: 'Ama Asante',
        role: 'Director, Elite Athletics',
        rating: 5,
    },
    {
        quote: 'Our membership grew by 65% since we started using Strongsuite. The reports help us make better decisions.',
        author: 'John Doe',
        role: 'CEO, Strong Bodies',
        rating: 5,
        metric: '+65% Growth',
    },
];

const pricingPlans = [
    {
        name: 'Starter',
        description: 'Perfect for small gyms and studios',
        price: { month: 29, year: 290 },
        features: [
            'Up to 50 members',
            'Basic reporting',
            'Email support',
            'Mobile app access',
            'QR code check-ins',
        ],
        cta: 'Get Started',
        popular: false,
    },
    {
        name: 'Professional',
        description: 'Ideal for growing gyms',
        price: { month: 79, year: 790 },
        features: [
            'Up to 200 members',
            'Advanced analytics',
            'Priority support',
            'All Starter features',
            'Class scheduling',
            'Payment integration',
            'Custom branding',
        ],
        cta: 'Start Free Trial',
        popular: true,
    },
    {
        name: 'Enterprise',
        description: 'For large facilities',
        price: { month: 199, year: 1990 },
        features: [
            'Unlimited members',
            'Custom integrations',
            'Dedicated account manager',
            'All Professional features',
            'Multi-location support',
            'Advanced API access',
            'Custom workflows',
        ],
        cta: 'Contact Sales',
        popular: false,
    },
];

const benefits = [
    {
        icon: Clock,
        title: '99.9% Uptime',
        description: 'Industry-leading reliability and performance',
    },
    {
        icon: Users,
        title: '24/7 Support',
        description: 'Always here when you need us',
    },
    {
        icon: ShieldCheck,
        title: 'Trusted by 500+ Gyms',
        description: 'Join successful gym owners worldwide',
    },
    {
        icon: ShieldCheck,
        title: 'Data Security',
        description: 'Bank-level encryption and security',
    },
];

// Counter animation
const animateCounter = (index: number) => {
    const stat = stats.value[index];
    const duration = 2000;
    const steps = 60;
    const increment = stat.target / steps;
    const stepDuration = duration / steps;
    
    let current = 0;
    const timer = setInterval(() => {
        current += increment;
        if (current >= stat.target) {
            stat.value = stat.target;
            clearInterval(timer);
        } else {
            stat.value = Math.floor(current);
        }
    }, stepDuration);
};

// Intersection Observer for scroll animations
onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        },
        { threshold: 0.1 }
    );

    document.querySelectorAll('.scroll-reveal').forEach((el) => {
        observer.observe(el);
    });

    // Animate stats when hero is visible
    const statsObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    stats.value.forEach((_, index) => {
                        setTimeout(() => animateCounter(index), index * 200);
                    });
                    statsObserver.disconnect();
                }
            });
        },
        { threshold: 0.5 }
    );

    const heroStats = document.querySelector('#hero-stats');
    if (heroStats) {
        statsObserver.observe(heroStats);
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href')!);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
</script>

<template>
    <Head title="Welcome to Strongsuite" />

    <div class="min-h-screen bg-gray-50 dark:bg-black transition-colors duration-300">
        <!-- Hero Section with Enhanced Background -->
        <section class="relative min-h-[85vh] sm:min-h-[90vh] overflow-hidden">
            <!-- Animated Background -->
            <div class="absolute inset-0 z-0">
                <img
                    src="/images/banner4.jpg"
                    alt="Gym Background"
                    class="h-full w-full object-cover"
                    fetchpriority="high"
                />
                <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-black/50 to-red-900/40 dark:from-black/80 dark:via-black/65 dark:to-crimson/25"></div>

                <!-- Animated Gradient Mesh -->
                <div class="absolute inset-0 bg-gradient-to-tr from-red-900/20 via-transparent to-orange-900/15 dark:from-crimson/15 dark:via-transparent dark:to-amber/8 animate-pulse"></div>
            </div>

            <!-- Navigation -->
            <nav class="relative z-10 border-b border-white/20 dark:border-white/10 backdrop-blur-md bg-black/20 dark:bg-transparent">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 sm:h-20 items-center justify-between">
                        <div class="flex items-center">
                            <img
                                src="/images/logo/strongsuite_white.png"
                                alt="StrongSuite"
                                class="h-8 sm:h-10 object-contain"
                            />
                        </div>

                        <!-- Desktop Navigation -->
                        <div class="hidden md:flex items-center gap-4">
                            <ThemeToggle />
                            <Link
                                v-if="$page.props.auth.user"
                                :href="dashboard()"
                                class="rounded-lg bg-crimson px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-crimson-light shadow-lg shadow-crimson/30"
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
                                    class="rounded-lg bg-crimson px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-crimson-light shadow-lg shadow-crimson/30"
                                >
                                    Get Started
                                </Link>
                            </template>
                        </div>

                        <!-- Mobile Menu Button -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden rounded-lg p-2 text-white/90 transition hover:bg-white/10"
                        >
                            <Menu v-if="!mobileMenuOpen" :size="24" />
                            <X v-else :size="24" />
                        </button>
                    </div>

                    <!-- Mobile Menu -->
                    <div
                        v-if="mobileMenuOpen"
                        class="md:hidden border-t border-white/20 py-4 space-y-3"
                    >
                        <div class="flex justify-center pb-3">
                            <ThemeToggle />
                        </div>
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="block rounded-lg bg-crimson px-6 py-3 text-center text-sm font-semibold text-white transition hover:bg-crimson-light shadow-lg shadow-crimson/30"
                            @click="mobileMenuOpen = false"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="block rounded-lg px-6 py-3 text-center text-sm font-semibold text-white/90 transition hover:bg-white/10"
                                @click="mobileMenuOpen = false"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="block rounded-lg bg-crimson px-6 py-3 text-center text-sm font-semibold text-white transition hover:bg-crimson-light shadow-lg shadow-crimson/30"
                                @click="mobileMenuOpen = false"
                            >
                                Get Started
                            </Link>
                        </template>
                        
                        <!-- Mobile Navigation Links -->
                        <a
                            href="#features"
                            class="block rounded-lg px-6 py-3 text-center text-sm font-semibold text-white/90 transition hover:bg-white/10"
                            @click="mobileMenuOpen = false"
                        >
                            Features
                        </a>
                        <a
                            href="#pricing"
                            class="block rounded-lg px-6 py-3 text-center text-sm font-semibold text-white/90 transition hover:bg-white/10"
                            @click="mobileMenuOpen = false"
                        >
                            Pricing
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Hero Content -->
            <div class="relative z-10 mx-auto max-w-7xl px-4 pt-8 pb-12 sm:pt-12 sm:pb-16 sm:px-6 lg:px-8 lg:pt-16 lg:pb-20">
                <div class="grid gap-6 sm:gap-8 lg:grid-cols-2 lg:gap-8 items-center">
                    <!-- Hero Text -->
                    <div class="flex flex-col justify-center scroll-reveal opacity-0 translate-y-8 transition-all duration-1000">
                        <h1 class="font-display font-bold tracking-tight leading-none">
                            <span class="block text-2xl sm:text-4xl md:text-5xl lg:text-5xl text-white">
                                TRANSFORM YOUR
                            </span>
                            <span class="block text-4xl sm:text-6xl md:text-7xl lg:text-8xl bg-gradient-to-r from-red-600 via-red-700 to-orange-600 dark:from-crimson-light dark:via-crimson dark:to-amber bg-clip-text text-transparent mt-1 sm:mt-2">
                                FITNESS BUSINESS
                            </span>
                        </h1>
                        <p class="mt-4 sm:mt-6 text-base sm:text-xl leading-7 sm:leading-8 text-gray-200 font-light">
                            Join Strongsuite and experience world-class gym management.
                            From personalized training to state-of-the-art equipment,
                            we're here to help you achieve your fitness goals.
                        </p>
                        <div class="mt-6 sm:mt-8 flex items-center gap-3 sm:gap-4 flex-wrap">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="register()"
                                class="group rounded-lg bg-red-600 dark:bg-crimson px-8 py-4 text-base font-semibold text-white transition-all hover:bg-red-700 dark:hover:bg-crimson-light hover:scale-105 shadow-xl shadow-red-600/40 dark:shadow-crimson/40"
                            >
                                <span class="flex items-center justify-center gap-2">
                                    Join Now
                                    <ArrowRight :size="18" class="transition-transform group-hover:translate-x-1" />
                                </span>
                            </Link>
                            <a
                                href="#features"
                                class="rounded-lg border-2 border-white/30 px-6 sm:px-8 py-3 sm:py-4 text-sm sm:text-base font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/10 hover:border-white/50 hover:scale-105"
                            >
                                Learn More
                            </a>
                        </div>
                    </div>

                    <!-- Hero Stats -->
                    <div id="hero-stats" class="grid grid-cols-2 gap-3 sm:gap-4 scroll-reveal opacity-0 translate-y-8 transition-all duration-1000 delay-300">
                        <div
                            v-for="(stat, index) in stats"
                            :key="index"
                            class="group rounded-xl sm:rounded-2xl border border-white/20 dark:border-crimson/30 bg-white/10 dark:bg-white/5 p-4 sm:p-8 backdrop-blur-md transition-all duration-300 hover:border-white/40 dark:hover:border-crimson/60 hover:bg-white/20 dark:hover:bg-crimson/10 hover:scale-105"
                        >
                            <div class="font-display text-3xl sm:text-5xl font-bold bg-gradient-to-r from-crimson-light to-amber bg-clip-text text-transparent">
                                {{ stat.value }}{{ stat.suffix }}
                            </div>
                            <div class="mt-1 sm:mt-2 text-xs sm:text-sm font-medium text-white/90">{{ stat.label }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section - Bento Grid -->
        <section id="features" class="bg-gradient-to-b from-gray-100 to-gray-200 dark:from-gray-950 dark:to-black py-16 sm:py-24 scroll-reveal opacity-0 translate-y-8 transition-all duration-1000">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 sm:mb-16">
                    <h2 class="font-display text-3xl sm:text-5xl md:text-6xl font-bold tracking-tight text-gray-900 dark:text-white">
                        EVERYTHING YOU NEED
                    </h2>
                    <p class="mt-3 sm:mt-4 text-base sm:text-xl text-gray-600 dark:text-gray-400 font-light">
                        Comprehensive gym management tools designed for your success
                    </p>
                </div>

                <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="group rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/10 bg-gradient-to-br from-white to-gray-50 dark:from-white/5 dark:to-transparent p-6 sm:p-8 backdrop-blur-sm transition-all duration-300 hover:border-red-300 dark:hover:border-crimson/50 hover:bg-gray-50 dark:hover:bg-white/10 hover:scale-105"
                    >
                        <div class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-orange-100 dark:from-crimson/20 dark:to-amber/20 transition-all group-hover:scale-110 group-hover:rotate-3">
                            <component :is="feature.icon" :size="24" class="sm:w-7 sm:h-7 text-red-600 dark:text-crimson-light" :stroke-width="2" />
                        </div>
                        <h3 class="mt-4 sm:mt-6 font-display text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ feature.title }}
                        </h3>
                        <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="bg-white dark:bg-black py-16 sm:py-24 scroll-reveal opacity-0 translate-y-8 transition-all duration-1000">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 sm:mb-16">
                    <h2 class="font-display text-3xl sm:text-5xl md:text-6xl font-bold tracking-tight text-gray-900 dark:text-white">
                        HOW IT WORKS
                    </h2>
                    <p class="mt-3 sm:mt-4 text-base sm:text-xl text-gray-600 dark:text-gray-400 font-light">
                        Get started in minutes
                    </p>
                </div>

                <div class="grid gap-6 sm:gap-8 grid-cols-1 md:grid-cols-3">
                    <div
                        v-for="(step, index) in steps"
                        :key="index"
                        class="group relative rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/10 bg-gradient-to-br from-white to-gray-50 dark:from-white/5 dark:to-transparent p-8 sm:p-10 backdrop-blur-sm transition-all duration-300 hover:border-red-300 dark:hover:border-crimson/50 hover:scale-105"
                    >
                        <div class="absolute -top-3 -left-3 sm:-top-4 sm:-left-4 font-display text-6xl sm:text-8xl font-bold text-red-200 dark:text-crimson/20 group-hover:text-red-300 dark:group-hover:text-crimson/30 transition-colors">
                            {{ step.number }}
                        </div>
                        <div class="relative flex flex-col items-center text-center">
                            <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-gradient-to-br from-red-600 to-orange-600 dark:from-crimson dark:to-amber mb-4 sm:mb-6">
                                <component :is="step.icon" :size="28" class="sm:w-8 sm:h-8 text-white" :stroke-width="2" />
                            </div>
                            <h3 class="font-display text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2 sm:mb-3">
                                {{ step.title }}
                            </h3>
                            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ step.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="bg-gradient-to-b from-gray-100 to-gray-200 dark:from-gray-950 dark:to-black py-16 sm:py-24 scroll-reveal opacity-0 translate-y-8 transition-all duration-1000">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 sm:mb-16">
                    <h2 class="font-display text-3xl sm:text-5xl md:text-6xl font-bold tracking-tight text-gray-900 dark:text-white">
                        WHAT OUR CLIENTS SAY
                    </h2>
                    <p class="mt-3 sm:mt-4 text-base sm:text-xl text-gray-600 dark:text-gray-400 font-light">
                        Real results from real gym owners
                    </p>
                </div>

                <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-2">
                    <div
                        v-for="(testimonial, index) in testimonials"
                        :key="index"
                        :class="[
                            'group rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/10 bg-gradient-to-br from-white to-gray-50 dark:from-white/5 dark:to-transparent p-6 sm:p-8 backdrop-blur-sm transition-all duration-300 hover:border-red-300 dark:hover:border-crimson/50 hover:scale-105',
                            testimonial.featured ? 'sm:row-span-2 sm:col-span-1' : ''
                        ]"
                    >
                        <Quote :size="32" class="sm:w-10 sm:h-10 text-red-400 dark:text-crimson-light/50 mb-3 sm:mb-4" />
                        <p :class="[
                            'text-gray-700 dark:text-gray-300 leading-relaxed mb-4 sm:mb-6',
                            testimonial.featured ? 'text-base sm:text-xl' : 'text-sm sm:text-base'
                        ]">
                            "{{ testimonial.quote }}"
                        </p>
                        
                        <div class="flex items-center gap-1 sm:gap-2 mb-3 sm:mb-4">
                            <Star v-for="i in testimonial.rating" :key="i" :size="14" class="sm:w-4 sm:h-4 text-amber fill-amber" />
                        </div>
                        
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-gradient-to-br from-red-600 to-orange-600 dark:from-crimson dark:to-amber flex items-center justify-center text-white font-bold text-sm sm:text-base">
                                {{ testimonial.author.charAt(0) }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white text-sm sm:text-base">{{ testimonial.author }}</div>
                                <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">{{ testimonial.role }}</div>
                            </div>
                        </div>
                        
                        <div v-if="testimonial.metric" class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200 dark:border-white/10">
                            <div class="font-display text-2xl sm:text-3xl font-bold bg-gradient-to-r from-red-600 to-orange-600 dark:from-crimson-light dark:to-amber bg-clip-text text-transparent">
                                {{ testimonial.metric }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="bg-white dark:bg-black py-16 sm:py-24 scroll-reveal opacity-0 translate-y-8 transition-all duration-1000">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 sm:mb-16">
                    <h2 class="font-display text-3xl sm:text-5xl md:text-6xl font-bold tracking-tight text-gray-900 dark:text-white">
                        CHOOSE YOUR PLAN
                    </h2>
                    <p class="mt-3 sm:mt-4 text-base sm:text-xl text-gray-600 dark:text-gray-400 font-light mb-6 sm:mb-8">
                        Flexible pricing for gyms of all sizes
                    </p>
                    
                    <!-- Monthly/Yearly Toggle -->
                    <div class="inline-flex items-center gap-2 sm:gap-3 rounded-full border border-gray-300 dark:border-white/20 bg-gray-100 dark:bg-white/5 p-1 backdrop-blur-sm">
                        <button
                            @click="pricingInterval = 'month'"
                            :class="[
                                'rounded-full px-4 sm:px-6 py-2 text-xs sm:text-sm font-semibold transition-all',
                                pricingInterval === 'month'
                                    ? 'bg-red-600 dark:bg-crimson text-white shadow-lg'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            Monthly
                        </button>
                        <button
                            @click="pricingInterval = 'year'"
                            :class="[
                                'rounded-full px-4 sm:px-6 py-2 text-xs sm:text-sm font-semibold transition-all',
                                pricingInterval === 'year'
                                    ? 'bg-red-600 dark:bg-crimson text-white shadow-lg'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            Yearly
                            <span class="ml-2 text-xs text-amber">Save 17%</span>
                        </button>
                    </div>
                </div>

                <div class="grid gap-6 sm:gap-8 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(plan, index) in pricingPlans"
                        :key="index"
                        :class="[
                            'group relative rounded-xl sm:rounded-2xl border p-6 sm:p-8 backdrop-blur-sm transition-all duration-300 hover:scale-105',
                            plan.popular
                                ? 'border-red-400 dark:border-crimson bg-gradient-to-br from-red-50 to-orange-50 dark:from-crimson/10 dark:to-amber/5 lg:scale-105 shadow-2xl shadow-red-200 dark:shadow-crimson/20'
                                : 'border-gray-200 dark:border-white/10 bg-gradient-to-br from-white to-gray-50 dark:from-white/5 dark:to-transparent hover:border-red-300 dark:hover:border-crimson/50'
                        ]"
                    >
                        <div v-if="plan.popular" class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-red-600 to-orange-600 dark:from-crimson dark:to-amber px-4 py-1 text-xs font-bold text-white shadow-lg">
                            MOST POPULAR
                        </div>
                        
                        <div class="text-center">
                            <h3 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                {{ plan.name }}
                            </h3>
                            <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">{{ plan.description }}</p>
                            
                            <div class="mt-4 sm:mt-6">
                                <span class="font-display text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">
                                    ${{ plan.price[pricingInterval] }}
                                </span>
                                <span class="text-gray-600 dark:text-gray-400">
                                    /{{ pricingInterval }}
                                </span>
                            </div>
                        </div>

                        <ul class="mt-6 sm:mt-8 space-y-3 sm:space-y-4">
                            <li
                                v-for="(feature, fIndex) in plan.features"
                                :key="fIndex"
                                class="flex items-start gap-3"
                            >
                                <CheckCircle :size="18" class="sm:w-5 sm:h-5 text-red-600 dark:text-crimson-light shrink-0 mt-0.5" />
                                <span class="text-sm sm:text-base text-gray-700 dark:text-gray-300">{{ feature }}</span>
                            </li>
                        </ul>

                        <Link
                            :href="plan.name === 'Enterprise' ? '#contact' : register()"
                            :class="[
                                'mt-6 sm:mt-8 block w-full rounded-lg px-6 py-3 text-center text-sm sm:text-base font-semibold transition-all',
                                plan.popular
                                    ? 'bg-gradient-to-r from-red-600 to-red-700 dark:from-crimson dark:to-crimson-light text-white shadow-lg shadow-red-200 dark:shadow-crimson/30 hover:shadow-xl hover:shadow-red-300 dark:hover:shadow-crimson/40'
                                    : 'bg-gray-200 dark:bg-white/10 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-white/20'
                            ]"
                        >
                            {{ plan.cta }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us Section -->
        <section class="bg-gradient-to-b from-gray-100 to-gray-200 dark:from-gray-950 dark:to-black py-16 sm:py-24 scroll-reveal opacity-0 translate-y-8 transition-all duration-1000">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 sm:mb-16">
                    <h2 class="font-display text-3xl sm:text-5xl md:text-6xl font-bold tracking-tight text-gray-900 dark:text-white">
                        WHY CHOOSE STRONGSUITE?
                    </h2>
                    <p class="mt-3 sm:mt-4 text-base sm:text-xl text-gray-600 dark:text-gray-400 font-light">
                        The complete solution for modern gym management
                    </p>
                </div>

                <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="(benefit, index) in benefits"
                        :key="index"
                        class="group rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/10 bg-gradient-to-br from-white to-gray-50 dark:from-white/5 dark:to-transparent p-6 sm:p-8 backdrop-blur-sm transition-all duration-300 hover:border-red-300 dark:hover:border-crimson/50 hover:scale-105 text-center"
                    >
                        <div class="flex justify-center mb-4 sm:mb-6">
                            <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-gradient-to-br from-red-100 to-orange-100 dark:from-crimson/20 dark:to-amber/20 transition-all group-hover:scale-110">
                                <component :is="benefit.icon" :size="28" class="sm:w-8 sm:h-8 text-red-600 dark:text-crimson-light" :stroke-width="2" />
                            </div>
                        </div>
                        <h3 class="font-display text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-2 sm:mb-3">
                            {{ benefit.title }}
                        </h3>
                        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                            {{ benefit.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="bg-white dark:bg-black py-16 sm:py-24 scroll-reveal opacity-0 translate-y-8 transition-all duration-1000">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 sm:mb-16">
                    <h2 class="font-display text-3xl sm:text-5xl md:text-6xl font-bold tracking-tight text-gray-900 dark:text-white">
                        OUR FACILITY
                    </h2>
                    <p class="mt-3 sm:mt-4 text-base sm:text-xl text-gray-600 dark:text-gray-400 font-light">
                        State-of-the-art equipment and modern training spaces
                    </p>
                </div>

                <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(image, index) in [
                            '/images/banner2.jpg',
                            '/images/banner3.jpg',
                            '/images/banner4.jpg',
                        ]"
                        :key="index"
                        class="group relative aspect-[4/3] overflow-hidden rounded-xl sm:rounded-2xl"
                    >
                        <img
                            :src="image"
                            :alt="`Gym Facility ${index + 1}`"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Section -->
        <section class="relative overflow-hidden py-20 sm:py-32 scroll-reveal opacity-0 translate-y-8 transition-all duration-1000">
            <div class="absolute inset-0 z-0">
                <img
                    src="/images/banner2.jpg"
                    alt="CTA Background"
                    class="h-full w-full object-cover"
                    loading="lazy"
                />
                <div class="absolute inset-0 bg-gradient-to-br from-black/95 via-crimson/40 to-black/90"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="font-display text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-bold tracking-tight text-white leading-none">
                        READY TO GET
                        <span class="block bg-gradient-to-r from-crimson-light via-amber to-crimson-light bg-clip-text text-transparent mt-1 sm:mt-2">
                            STARTED?
                        </span>
                    </h2>
                    <p class="mt-4 sm:mt-8 text-lg sm:text-2xl text-gray-300 dark:text-gray-300 font-light px-4">
                        Join hundreds of members who trust Strongsuite for their fitness journey
                    </p>
                    <div class="mt-8 sm:mt-12 flex flex-col sm:flex-row justify-center gap-3 sm:gap-4 px-4">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="register()"
                            class="group rounded-lg bg-gradient-to-r from-red-600 to-red-700 dark:from-crimson dark:to-crimson-light px-8 sm:px-10 py-4 sm:py-5 text-base sm:text-lg font-bold text-white transition-all hover:shadow-2xl hover:shadow-red-500/50 dark:hover:shadow-crimson/50 hover:scale-105"
                        >
                            <span class="flex items-center justify-center gap-2">
                                Join Now
                                <ArrowRight :size="20" class="sm:w-6 sm:h-6 transition-transform group-hover:translate-x-1" />
                            </span>
                        </Link>
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="login()"
                            class="rounded-lg border-2 border-white/30 px-8 sm:px-10 py-4 sm:py-5 text-base sm:text-lg font-bold text-white backdrop-blur-sm transition-all hover:bg-white/10 hover:border-white/50 hover:scale-105"
                        >
                            Sign In
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 dark:border-white/10 bg-gradient-to-b from-gray-100 to-gray-200 dark:from-gray-950 dark:to-black">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:py-16 sm:px-6 lg:px-8">
                <div class="grid gap-8 sm:gap-12 grid-cols-1 sm:grid-cols-2 md:grid-cols-4">
                    <div class="sm:col-span-2">
                        <img
                            src="/images/logo/strongsuite_white.png"
                            alt="StrongSuite"
                            class="h-8 sm:h-10 object-contain mb-4 sm:mb-6"
                        />
                        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed max-w-md">
                            Modern gym management system built for Ghana and the world.
                            Manage members, payments, classes, and more with ease.
                        </p>
                    </div>

                    <div>
                        <h4 class="font-display text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                            QUICK LINKS
                        </h4>
                        <ul class="space-y-2 sm:space-y-3">
                            <li>
                                <a href="#features" class="text-sm sm:text-base text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-crimson-light transition">
                                    Features
                                </a>
                            </li>
                            <li>
                                <Link :href="login()" class="text-sm sm:text-base text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-crimson-light transition">
                                    Login
                                </Link>
                            </li>
                            <li v-if="canRegister">
                                <Link :href="register()" class="text-sm sm:text-base text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-crimson-light transition">
                                    Register
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-display text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                            CONTACT
                        </h4>
                        <ul class="space-y-2 sm:space-y-3">
                            <li class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                                info@strongsuite.com
                            </li>
                            <li class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                                +233 XX XXX XXXX
                            </li>
                            <li class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                                Accra, Ghana
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 sm:mt-12 border-t border-gray-200 dark:border-white/10 pt-6 sm:pt-8">
                    <p class="text-center text-sm sm:text-base text-gray-600 dark:text-gray-400">
                        © {{ new Date().getFullYear() }} Strongsuite. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.scroll-reveal {
    opacity: 0;
    transform: translateY(30px);
}

.scroll-reveal.animate-in {
    opacity: 1;
    transform: translateY(0);
}
</style>