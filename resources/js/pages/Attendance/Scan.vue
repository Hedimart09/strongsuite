<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Attendance', href: '/attendance' },
    { title: 'Scan QR Code', href: '/attendance/scan' },
];

const form = useForm({
    qr_code: '',
});

const qrInput = ref<HTMLInputElement | null>(null);

onMounted(() => {
    qrInput.value?.focus();
});

const submit = () => {
    form.post('/attendance/check-in/qr', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            qrInput.value?.focus();
        },
    });
};
</script>

<template>
    <Head title="Scan QR Code" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    QR Code Check-In
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Scan member QR code to check in or check out
                </p>
            </div>

            <div class="mx-auto w-full max-w-2xl">
                <div
                    class="rounded-xl border border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
                >
                    <div class="mb-8">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mx-auto h-32 w-32 text-muted-foreground/30"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                            />
                        </svg>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label
                                for="qr_code"
                                class="mb-2 block text-sm font-medium"
                                >Scan QR Code</label
                            >
                            <input
                                id="qr_code"
                                ref="qrInput"
                                v-model="form.qr_code"
                                type="text"
                                placeholder="QR-XXXXXXXXXXXX"
                                required
                                autofocus
                                class="w-full rounded-lg border border-input bg-background px-4 py-3 text-center font-mono text-lg ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500': form.errors.qr_code,
                                }"
                            />
                            <p
                                v-if="form.errors.qr_code"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ form.errors.qr_code }}
                            </p>
                            <p class="mt-2 text-xs text-muted-foreground">
                                Click in the input field above and scan the QR
                                code with a barcode scanner
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-3 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Processing...'
                                    : 'Check In/Out'
                            }}
                        </button>
                    </form>

                    <div class="mt-8 rounded-lg bg-sidebar/50 p-4">
                        <p class="text-xs text-muted-foreground">
                            <strong>Instructions:</strong>
                        </p>
                        <ul
                            class="mt-2 list-inside list-disc space-y-1 text-left text-xs text-muted-foreground"
                        >
                            <li>
                                Use a barcode scanner to scan the member's QR
                                code
                            </li>
                            <li>
                                The system will automatically check in or check
                                out the member
                            </li>
                            <li>
                                If the member is already checked in, scanning
                                will check them out
                            </li>
                            <li>
                                You can also manually type the QR code if needed
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a
                        href="/attendance"
                        class="inline-flex items-center justify-center rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        View Attendance Log
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
