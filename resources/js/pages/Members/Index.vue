<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Member {
    id: number;
    member_id: string;
    name: string;
    email: string;
    phone: string;
    status: string;
    photo: string | null;
    created_at: string;
    subscriptions?: any[];
}

interface Props {
    members: {
        data: Member[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Members',
        href: '/members',
    },
];

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

watch([search, status], () => {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (status.value) params.status = status.value;

    router.get('/members', params, {
        preserveState: true,
        preserveScroll: true,
    });
}, { debounce: 300 });

const getStatusClass = (memberStatus: string) => {
    return memberStatus === 'active'
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
};
</script>

<template>
    <Head title="Members" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Members</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Manage gym members and their information
                    </p>
                </div>
                <a
                    href="/members/create"
                    class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
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
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Register Member
                </a>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium mb-2">Search</label>
                        <input
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Search by name, email, member ID, or phone..."
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium mb-2">Status</label>
                        <select
                            id="status"
                            v-model="status"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Members List -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-sidebar-border bg-sidebar">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Member</th>
                                <th class="px-4 py-3 text-left font-medium">Member ID</th>
                                <th class="px-4 py-3 text-left font-medium">Contact</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">Joined</th>
                                <th class="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/50">
                            <tr
                                v-for="member in members.data"
                                :key="member.id"
                                class="hover:bg-sidebar/50 transition-colors"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            v-if="member.photo"
                                            class="h-10 w-10 rounded-full overflow-hidden"
                                        >
                                            <img
                                                :src="`/storage/${member.photo}`"
                                                :alt="member.name"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center"
                                        >
                                            <span class="text-sm font-medium text-primary">
                                                {{ member.name.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                        <span class="font-medium">{{ member.name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <code class="text-xs bg-sidebar px-2 py-1 rounded">
                                        {{ member.member_id }}
                                    </code>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-1">
                                        <div class="text-xs text-muted-foreground">{{ member.email }}</div>
                                        <div class="text-xs text-muted-foreground">{{ member.phone }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="getStatusClass(member.status)"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ member.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ new Date(member.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a
                                            :href="`/members/${member.id}`"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            View
                                        </a>
                                        <a
                                            :href="`/members/${member.id}/edit`"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            Edit
                                        </a>
                                        <a
                                            :href="`/members/${member.id}/qr-code`"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            QR Code
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="members.data.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                    No members found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="members.last_page > 1"
                    class="border-t border-sidebar-border px-4 py-3 flex items-center justify-between"
                >
                    <div class="text-sm text-muted-foreground">
                        Showing {{ ((members.current_page - 1) * members.per_page) + 1 }}
                        to {{ Math.min(members.current_page * members.per_page, members.total) }}
                        of {{ members.total }} members
                    </div>
                    <div class="flex gap-2">
                        <a
                            v-for="(link, index) in members.links"
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
