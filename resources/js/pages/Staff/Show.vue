<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Staff {
    id: number;
    name: string;
    email: string;
    created_at: string;
    roles: Array<{ name: string }>;
    permissions: Array<{ name: string }>;
    all_permissions: Array<{ name: string }>;
}

interface Props {
    staff: Staff;
}

defineProps<Props>();
</script>

<template>
    <Head :title="`Staff: ${staff.name}`" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ staff.name }}</h1>
                <div class="flex gap-2">
                    <Link
                        :href="`/staff/${staff.id}/edit`"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                    >
                        Edit
                    </Link>
                    <Link
                        href="/staff"
                        class="rounded-lg border px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        Back to List
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Basic Info -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">
                        Basic Information
                    </h2>
                    <dl class="space-y-3">
                        <div>
                            <dt
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                Name
                            </dt>
                            <dd class="font-medium">{{ staff.name }}</dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                Email
                            </dt>
                            <dd class="font-medium">{{ staff.email }}</dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                Member Since
                            </dt>
                            <dd class="font-medium">
                                {{
                                    new Date(
                                        staff.created_at,
                                    ).toLocaleDateString()
                                }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Roles & Permissions -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold">
                        Roles & Permissions
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <h3
                                class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                Roles
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="role in staff.roles"
                                    :key="role.name"
                                    class="rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                >
                                    {{ role.name }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <h3
                                class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                Permissions
                            </h3>
                            <div
                                v-if="
                                    staff.all_permissions &&
                                    staff.all_permissions.length > 0
                                "
                                class="flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="permission in staff.all_permissions"
                                    :key="permission.name"
                                    class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                >
                                    {{ permission.name }}
                                </span>
                            </div>
                            <p
                                v-else
                                class="text-sm text-gray-500 italic dark:text-gray-400"
                            >
                                No permissions assigned
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
