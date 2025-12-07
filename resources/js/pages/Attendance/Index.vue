<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Member {
    id: number;
    name: string;
    member_id: string;
    photo: string | null;
}

interface Attendance {
    id: number;
    check_in_time: string;
    check_out_time: string | null;
    check_in_method: string;
    duration: number | null;
    member: Member;
}

interface Props {
    attendances: {
        data: Attendance[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        date?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Attendance', href: '/attendance' },
];

const date = ref(props.filters.date || new Date().toISOString().split('T')[0]);
const search = ref(props.filters.search || '');

watch([date, search], () => {
    const params: Record<string, string> = {};
    if (date.value) params.date = date.value;
    if (search.value) params.search = search.value;

    router.get('/attendance', params, {
        preserveState: true,
        preserveScroll: true,
    });
}, { debounce: 300 });

const formatTime = (datetime: string) => {
    return new Date(datetime).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDuration = (minutes: number | null) => {
    if (!minutes) return 'Still checked in';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hours > 0) {
        return `${hours}h ${mins}m`;
    }
    return `${mins}m`;
};

const getMethodBadge = (method: string) => {
    return method === 'qr_code'
        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
};
</script>

<template>
    <Head title="Attendance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Attendance Log</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Track member check-ins and check-outs
                    </p>
                </div>
                <div class="flex gap-2">
                    <a
                        href="/attendance/manual"
                        class="inline-flex items-center justify-center rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        Manual Check-In
                    </a>
                    <a
                        href="/attendance/scan"
                        class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-2"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                            />
                        </svg>
                        Scan QR Code
                    </a>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="date" class="block text-sm font-medium mb-2">Date</label>
                        <input
                            id="date"
                            v-model="date"
                            type="date"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium mb-2">Search</label>
                        <input
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Search by name or member ID..."
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-sidebar-border bg-sidebar">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Member</th>
                                <th class="px-4 py-3 text-left font-medium">Check In</th>
                                <th class="px-4 py-3 text-left font-medium">Check Out</th>
                                <th class="px-4 py-3 text-left font-medium">Duration</th>
                                <th class="px-4 py-3 text-left font-medium">Method</th>
                                <th class="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/50">
                            <tr
                                v-for="attendance in attendances.data"
                                :key="attendance.id"
                                class="hover:bg-sidebar/50 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            v-if="attendance.member.photo"
                                            class="h-8 w-8 rounded-full overflow-hidden"
                                        >
                                            <img
                                                :src="`/storage/${attendance.member.photo}`"
                                                :alt="attendance.member.name"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center"
                                        >
                                            <span class="text-xs font-medium text-primary">
                                                {{ attendance.member.name.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ attendance.member.name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ attendance.member.member_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ formatTime(attendance.check_in_time) }}</td>
                                <td class="px-4 py-3">
                                    {{ attendance.check_out_time ? formatTime(attendance.check_out_time) : '-' }}
                                </td>
                                <td class="px-4 py-3">{{ formatDuration(attendance.duration) }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="getMethodBadge(attendance.check_in_method)"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ attendance.check_in_method === 'qr_code' ? 'QR Code' : 'Manual' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <form
                                        v-if="!attendance.check_out_time"
                                        :action="`/attendance/${attendance.id}/check-out`"
                                        method="post"
                                        class="inline"
                                    >
                                        <button
                                            type="submit"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            Check Out
                                        </button>
                                    </form>
                                    <span v-else class="text-xs text-muted-foreground">-</span>
                                </td>
                            </tr>
                            <tr v-if="attendances.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                    No attendance records found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="attendances.last_page > 1"
                    class="border-t border-sidebar-border px-4 py-3 flex items-center justify-between"
                >
                    <div class="text-sm text-muted-foreground">
                        Showing {{ ((attendances.current_page - 1) * attendances.per_page) + 1 }}
                        to {{ Math.min(attendances.current_page * attendances.per_page, attendances.total) }}
                        of {{ attendances.total }} records
                    </div>
                    <div class="flex gap-2">
                        <a
                            v-for="(link, index) in attendances.links"
                            :key="index"
                            :href="link.url"
                            :class="[
                                link.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-sidebar hover:bg-sidebar-accent',
                                !link.url && 'opacity-50 cursor-not-allowed',
                            ]"
                            class="px-3 py-1 rounded text-sm"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
