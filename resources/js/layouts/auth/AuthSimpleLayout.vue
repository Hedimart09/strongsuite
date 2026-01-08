<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden p-6 md:p-10"
    >
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 z-0">
            <img
                src="/images/banner4.jpg"
                alt="Auth Background"
                class="h-full w-full object-cover"
            />
            <div
                class="absolute inset-0 bg-gradient-to-br from-black/85 via-black/80 to-black/85"
            ></div>
        </div>

        <!-- Auth Form Container -->
        <div class="relative z-10 w-full max-w-md">
            <div
                class="flex flex-col gap-8 rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl md:p-10"
            >
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/20 backdrop-blur-sm"
                        >
                            <AppLogoIcon
                                class="size-9 fill-current text-white"
                            />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-2xl font-bold text-white">
                            {{ title }}
                        </h1>
                        <p class="text-center text-sm text-gray-300">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
