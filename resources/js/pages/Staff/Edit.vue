<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Staff {
    id: number;
    name: string;
    email: string;
    roles: Array<{ name: string }>;
}

interface Props {
    staff: Staff;
    roles: Array<{ name: string }>;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.staff.name,
    email: props.staff.email,
    password: '',
    password_confirmation: '',
    role: props.staff.roles[0]?.name || '',
});

const submit = () => {
    form.put(`/staff/${props.staff.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Staff Member" />

    <AppLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Edit Staff Member</h1>
            </div>

            <div class="max-w-2xl rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border px-4 py-2 dark:bg-gray-700"
                            required
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-lg border px-4 py-2 dark:bg-gray-700"
                            required
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Password (leave blank to keep current)
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full rounded-lg border px-4 py-2 dark:bg-gray-700"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password -->
                    <div v-if="form.password">
                        <label class="mb-2 block text-sm font-medium">
                            Confirm Password
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full rounded-lg border px-4 py-2 dark:bg-gray-700"
                        />
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">Role</label>
                        <select
                            v-model="form.role"
                            class="w-full rounded-lg border px-4 py-2 dark:bg-gray-700"
                            required
                        >
                            <option value="">Select a role</option>
                            <option v-for="role in roles" :key="role.name" :value="role.name">
                                {{ role.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.role" />
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-4">
                        <Link
                            :href="`/staff/${staff.id}`"
                            class="rounded-lg border px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Update Staff Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
