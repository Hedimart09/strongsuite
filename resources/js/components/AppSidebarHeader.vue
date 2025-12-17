<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType, GymSettings } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const gymSettings = computed(() => page.props.gymSettings as GymSettings | null);

const gymName = computed(() => gymSettings.value?.gym_name || (page.props.name as string));
const gymLogo = computed(() =>
    gymSettings.value?.logo ? `/storage/${gymSettings.value.logo}` : null
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/20">
                <img
                    v-if="gymLogo"
                    :src="gymLogo"
                    :alt="`${gymName} Logo`"
                    class="h-full w-full object-cover"
                />
                <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-6 w-6 text-primary"
                >
                    <path d="M14.4 14.4 9.6 9.6" />
                    <path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z" />
                    <path d="m21.5 21.5-1.4-1.4" />
                    <path d="M3.9 3.9 2.5 2.5" />
                    <path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z" />
                </svg>
            </div>
            <div class="hidden text-right leading-tight sm:block">
                <p class="text-sm font-semibold text-foreground">{{ gymName }}</p>
                <p class="text-xs text-muted-foreground">Till bones break</p>
            </div>
        </div>
    </header>
</template>
