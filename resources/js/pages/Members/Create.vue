<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Members',
        href: '/members',
    },
    {
        title: 'Register New Member',
        href: '/members/create',
    },
];

const form = useForm({
    name: '',
    email: '',
    phone: '',
    date_of_birth: '',
    gender: '',
    address: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    photo: null as File | null,
});

const photoPreview = ref<string | null>(null);

const handlePhotoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.photo = target.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(target.files[0]);
    }
};

const submit = () => {
    form.post('/members', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Register New Member" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Register New Member
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Add a new member to the gym
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Personal Information -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 text-lg font-semibold">
                        Personal Information
                    </h2>

                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-sm font-medium"
                                >Full Name *</label
                            >
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-medium"
                                >Email Address *</label
                            >
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{ 'border-red-500': form.errors.email }"
                            />
                            <p
                                v-if="form.errors.email"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label
                                for="phone"
                                class="mb-2 block text-sm font-medium"
                                >Phone Number *</label
                            >
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{ 'border-red-500': form.errors.phone }"
                            />
                            <p
                                v-if="form.errors.phone"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label
                                for="date_of_birth"
                                class="mb-2 block text-sm font-medium"
                                >Date of Birth *</label
                            >
                            <input
                                id="date_of_birth"
                                v-model="form.date_of_birth"
                                type="date"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500': form.errors.date_of_birth,
                                }"
                            />
                            <p
                                v-if="form.errors.date_of_birth"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.date_of_birth }}
                            </p>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label
                                for="gender"
                                class="mb-2 block text-sm font-medium"
                                >Gender *</label
                            >
                            <select
                                id="gender"
                                v-model="form.gender"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500': form.errors.gender,
                                }"
                            >
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <p
                                v-if="form.errors.gender"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.gender }}
                            </p>
                        </div>

                        <!-- Photo -->
                        <div>
                            <label
                                for="photo"
                                class="mb-2 block text-sm font-medium"
                                >Photo (Optional)</label
                            >
                            <input
                                id="photo"
                                @change="handlePhotoChange"
                                type="file"
                                accept="image/*"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{ 'border-red-500': form.errors.photo }"
                            />
                            <p
                                v-if="form.errors.photo"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.photo }}
                            </p>
                            <div v-if="photoPreview" class="mt-2">
                                <img
                                    :src="photoPreview"
                                    alt="Photo preview"
                                    class="h-20 w-20 rounded-lg object-cover"
                                />
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label
                                for="address"
                                class="mb-2 block text-sm font-medium"
                                >Address (Optional)</label
                            >
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500': form.errors.address,
                                }"
                            ></textarea>
                            <p
                                v-if="form.errors.address"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.address }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 text-lg font-semibold">
                        Emergency Contact
                    </h2>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label
                                for="emergency_contact_name"
                                class="mb-2 block text-sm font-medium"
                                >Contact Name *</label
                            >
                            <input
                                id="emergency_contact_name"
                                v-model="form.emergency_contact_name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500':
                                        form.errors.emergency_contact_name,
                                }"
                            />
                            <p
                                v-if="form.errors.emergency_contact_name"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.emergency_contact_name }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="emergency_contact_phone"
                                class="mb-2 block text-sm font-medium"
                                >Contact Phone *</label
                            >
                            <input
                                id="emergency_contact_phone"
                                v-model="form.emergency_contact_phone"
                                type="tel"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500':
                                        form.errors.emergency_contact_phone,
                                }"
                            />
                            <p
                                v-if="form.errors.emergency_contact_phone"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.emergency_contact_phone }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-4">
                    <a
                        href="/members"
                        class="inline-flex items-center justify-center rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? 'Registering...'
                                : 'Register Member'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
