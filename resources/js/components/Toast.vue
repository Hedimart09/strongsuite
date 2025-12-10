<script setup lang="ts">
import { CheckCircle2, XCircle, AlertCircle, X } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface FlashMessage {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

const page = usePage<{ flash: FlashMessage }>();
const showToast = ref(false);
const message = ref('');
const type = ref<'success' | 'error' | 'warning' | 'info'>('success');

const toastClasses = computed(() => {
    const baseClasses = 'fixed top-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg border transition-all duration-300 max-w-md';

    switch (type.value) {
        case 'success':
            return `${baseClasses} bg-green-50 dark:bg-green-950 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200`;
        case 'error':
            return `${baseClasses} bg-red-50 dark:bg-red-950 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200`;
        case 'warning':
            return `${baseClasses} bg-yellow-50 dark:bg-yellow-950 border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200`;
        case 'info':
            return `${baseClasses} bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200`;
        default:
            return baseClasses;
    }
});

const iconComponent = computed(() => {
    switch (type.value) {
        case 'success':
            return CheckCircle2;
        case 'error':
            return XCircle;
        case 'warning':
        case 'info':
            return AlertCircle;
        default:
            return CheckCircle2;
    }
});

const iconClasses = computed(() => {
    switch (type.value) {
        case 'success':
            return 'text-green-600 dark:text-green-400';
        case 'error':
            return 'text-red-600 dark:text-red-400';
        case 'warning':
            return 'text-yellow-600 dark:text-yellow-400';
        case 'info':
            return 'text-blue-600 dark:text-blue-400';
        default:
            return 'text-green-600 dark:text-green-400';
    }
});

const closeToast = () => {
    showToast.value = false;
};

const autoHide = () => {
    setTimeout(() => {
        showToast.value = false;
    }, 5000); // Hide after 5 seconds
};

watch(
    () => page.props.flash,
    (flash) => {
        if (flash.success) {
            message.value = flash.success;
            type.value = 'success';
            showToast.value = true;
            autoHide();
        } else if (flash.error) {
            message.value = flash.error;
            type.value = 'error';
            showToast.value = true;
            autoHide();
        } else if (flash.warning) {
            message.value = flash.warning;
            type.value = 'warning';
            showToast.value = true;
            autoHide();
        } else if (flash.info) {
            message.value = flash.info;
            type.value = 'info';
            showToast.value = true;
            autoHide();
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-x-full"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 translate-x-full"
    >
        <div v-if="showToast" :class="toastClasses" role="alert">
            <component :is="iconComponent" :class="iconClasses" class="size-5 flex-shrink-0" />
            <p class="flex-1 text-sm font-medium">{{ message }}</p>
            <button
                @click="closeToast"
                class="flex-shrink-0 rounded-md p-1 hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
                aria-label="Close"
            >
                <X class="size-4" />
            </button>
        </div>
    </Transition>
</template>
