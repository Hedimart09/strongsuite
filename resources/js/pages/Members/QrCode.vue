<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Member {
    id: number;
    member_id: string;
    name: string;
    email: string;
    phone: string;
    photo: string | null;
    qr_code: string;
}

interface Props {
    member: Member;
    qrCodeSvg: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Members',
        href: '/members',
    },
    {
        title: props.member.name,
        href: `/members/${props.member.id}`,
    },
    {
        title: 'QR Code',
        href: `/members/${props.member.id}/qr-code`,
    },
];

const printQrCode = () => {
    window.print();
};

const downloadQrCode = () => {
    const svg = props.qrCodeSvg;
    const blob = new Blob([svg], { type: 'image/svg+xml' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${props.member.member_id}-qr-code.svg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};
</script>

<template>
    <Head :title="`QR Code - ${member.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <!-- Header -->
            <div class="flex items-center justify-between print:hidden">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Member QR Code
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Scan this code for quick check-in
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="downloadQrCode"
                        class="inline-flex items-center justify-center rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mr-2 h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                            />
                        </svg>
                        Download
                    </button>
                    <button
                        @click="printQrCode"
                        class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mr-2 h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                            />
                        </svg>
                        Print
                    </button>
                </div>
            </div>

            <!-- QR Code Card -->
            <div class="mx-auto max-w-2xl">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white p-8 text-center dark:border-sidebar-border dark:bg-sidebar"
                >
                    <!-- Member Info -->
                    <div class="mb-6">
                        <div
                            v-if="member.photo"
                            class="mx-auto mb-4 h-20 w-20 overflow-hidden rounded-full"
                        >
                            <img
                                :src="`/storage/${member.photo}`"
                                :alt="member.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10"
                        >
                            <span class="text-2xl font-medium text-primary">
                                {{ member.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <h2 class="text-xl font-bold">{{ member.name }}</h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ member.email }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ member.phone }}
                        </p>
                    </div>

                    <!-- QR Code -->
                    <div class="inline-block rounded-lg bg-white p-8">
                        <div v-html="qrCodeSvg" class="mx-auto"></div>
                    </div>

                    <!-- Member ID -->
                    <div class="mt-6">
                        <p class="text-sm font-medium text-muted-foreground">
                            Member ID
                        </p>
                        <code
                            class="mt-2 inline-block rounded bg-sidebar px-4 py-2 font-mono text-lg"
                        >
                            {{ member.member_id }}
                        </code>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm font-medium text-muted-foreground">
                            QR Code
                        </p>
                        <code
                            class="mt-2 inline-block rounded bg-sidebar px-4 py-2 font-mono text-sm"
                        >
                            {{ member.qr_code }}
                        </code>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-8 rounded-lg bg-sidebar/50 p-4 print:hidden">
                        <p class="text-xs text-muted-foreground">
                            <strong>Instructions:</strong> Present this QR code
                            at the gym entrance for quick check-in. You can
                            print this card or save the QR code to your phone.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="text-center print:hidden">
                <a
                    :href="`/members/${member.id}`"
                    class="inline-flex items-center justify-center rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground"
                >
                    Back to Profile
                </a>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    .print\:hidden {
        display: none !important;
    }
}
</style>
