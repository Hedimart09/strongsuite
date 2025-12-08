<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Role {
    id: number;
    name: string;
}

interface StaffMember {
    id: number;
    name: string;
    email: string;
    created_at: string;
    roles: Role[];
}

interface Props {
    staff: {
        data: StaffMember[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        role?: string;
        search?: string;
    };
    roles: Role[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Staff', href: '/staff' },
];

const role = ref(props.filters.role || '');
const search = ref(props.filters.search || '');

watch([role, search], () => {
    const params: Record<string, string> = {};
    if (role.value) params.role = role.value;
    if (search.value) params.search = search.value;

    router.get('/staff', params, {
        preserveState: true,
        preserveScroll: true,
    });
}, { debounce: 300 });

const getRoleClass = (roleName: string) => {
    const classes: Record<string, string> = {
        'Admin': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        'Receptionist': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'Trainer': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    };
    return classes[roleName] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
};
</script>

<template>
    <Head title="Staff" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Staff</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Manage staff members and their roles
                </p>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="role" class="block text-sm font-medium mb-2">Role</label>
                        <select
                            id="role"
                            v-model="role"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="">All Roles</option>
                            <option v-for="r in roles" :key="r.id" :value="r.name">
                                {{ r.name }}
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium mb-2">Search</label>
                        <input
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Search by name or email..."
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
                                <th class="px-4 py-3 text-left font-medium">Name</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-left font-medium">Role</th>
                                <th class="px-4 py-3 text-left font-medium">Joined</th>
                                <th class="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/50">
                            <tr
                                v-for="member in staff.data"
                                :key="member.id"
                                class="hover:bg-sidebar/50 transition-colors"
                            >
                                <td class="px-4 py-3 font-medium">{{ member.name }}</td>
                                <td class="px-4 py-3">{{ member.email }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        v-for="r in member.roles"
                                        :key="r.id"
                                        :class="getRoleClass(r.name)"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ r.name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ new Date(member.created_at).toLocaleDateString() }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a
                                            :href="`/staff/${member.id}`"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            View
                                        </a>
                                        <a
                                            :href="`/staff/${member.id}/edit`"
                                            class="text-sm text-primary hover:text-primary/80"
                                        >
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="staff.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                    No staff members found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="staff.last_page > 1"
                    class="border-t border-sidebar-border px-4 py-3 flex items-center justify-between"
                >
                    <div class="text-sm text-muted-foreground">
                        Showing {{ ((staff.current_page - 1) * staff.per_page) + 1 }}
                        to {{ Math.min(staff.current_page * staff.per_page, staff.total) }}
                        of {{ staff.total }} records
                    </div>
                    <div class="flex gap-2">
                        <a
                            v-for="(link, index) in staff.links"
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
