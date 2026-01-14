<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';

const { appearance, updateAppearance } = useAppearance();

const isDark = computed(() => {
    if (typeof window === 'undefined') return false;
    if (appearance.value === 'system') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    return appearance.value === 'dark';
});

const toggleTheme = () => {
    const newTheme = isDark.value ? 'light' : 'dark';
    updateAppearance(newTheme);
    console.log('Theme toggled to:', newTheme);
};

onMounted(() => {
    console.log('Current appearance:', appearance.value);
    console.log('Is dark:', isDark.value);
});
</script>

<template>
    <button
        @click="toggleTheme"
        class="h-9 w-9 rounded-md p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    >
        <Sun v-if="isDark" class="h-5 w-5" />
        <Moon v-else class="h-5 w-5" />
    </button>
</template>