<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useSidebar } from '@/components/ui/sidebar/utils';

const { state } = useSidebar();
</script>

<template>
    <div class="flex items-center">
        <!-- Show logomark when sidebar is collapsed -->
        <div v-if="state === 'collapsed'">
            <AppLogoIcon class="size-8" />
        </div>

        <!-- Show full logo when sidebar is expanded -->
        <div v-else>
            <img
                src="/images/logo/strongsuite_logo.png"
                alt="StrongSuite"
                class="h-8 object-contain dark:hidden"
            />
            <img
                src="/images/logo/strongsuite_white.png"
                alt="StrongSuite"
                class="hidden h-8 object-contain dark:block"
            />
        </div>
    </div>
</template>
