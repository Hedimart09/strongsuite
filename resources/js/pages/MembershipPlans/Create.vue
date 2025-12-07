<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Membership Plans', href: '/membership-plans' },
    { title: 'Create', href: '/membership-plans/create' },
];

const form = useForm({
    name: '',
    description: '',
    price: '',
    currency: 'GHS',
    duration_in_days: '',
    is_active: true,
    features: [''],
});

const addFeature = () => {
    form.features.push('');
};

const removeFeature = (index: number) => {
    form.features.splice(index, 1);
};

const submit = () => {
    const filteredFeatures = form.features.filter(f => f.trim() !== '');
    form.transform((data) => ({
        ...data,
        features: filteredFeatures.length > 0 ? filteredFeatures : null,
    })).post('/membership-plans');
};
</script>

<template>
    <Head title="Create Membership Plan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Create Membership Plan</h1>
                <p class="text-sm text-muted-foreground mt-1">Add a new membership plan</p>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Plan Details -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <h2 class="text-lg font-semibold mb-4">Plan Details</h2>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium mb-2">Plan Name *</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium mb-2">Description</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                :class="{ 'border-red-500': form.errors.description }"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium mb-2">Price *</label>
                            <input
                                id="price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                :class="{ 'border-red-500': form.errors.price }"
                            />
                            <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium mb-2">Currency *</label>
                            <select
                                id="currency"
                                v-model="form.currency"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                :class="{ 'border-red-500': form.errors.currency }"
                            >
                                <option value="GHS">GHS (Ghanaian Cedi)</option>
                                <option value="USD">USD (US Dollar)</option>
                                <option value="EUR">EUR (Euro)</option>
                                <option value="GBP">GBP (British Pound)</option>
                            </select>
                            <p v-if="form.errors.currency" class="mt-1 text-sm text-red-600">{{ form.errors.currency }}</p>
                        </div>

                        <div>
                            <label for="duration_in_days" class="block text-sm font-medium mb-2">Duration (days) *</label>
                            <input
                                id="duration_in_days"
                                v-model="form.duration_in_days"
                                type="number"
                                min="1"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                :class="{ 'border-red-500': form.errors.duration_in_days }"
                            />
                            <p class="mt-1 text-xs text-muted-foreground">Common: 7 (week), 30 (month), 90 (3 months), 365 (year)</p>
                            <p v-if="form.errors.duration_in_days" class="mt-1 text-sm text-red-600">{{ form.errors.duration_in_days }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Status</label>
                            <div class="flex items-center gap-2">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="rounded border-input"
                                />
                                <label for="is_active" class="text-sm">Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Features</h2>
                        <button
                            type="button"
                            @click="addFeature"
                            class="text-sm text-primary hover:text-primary/80"
                        >
                            + Add Feature
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(feature, index) in form.features" :key="index" class="flex gap-2">
                            <input
                                v-model="form.features[index]"
                                type="text"
                                placeholder="Feature description"
                                class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            />
                            <button
                                v-if="form.features.length > 1"
                                type="button"
                                @click="removeFeature(index)"
                                class="text-red-500 hover:text-red-600"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 justify-end">
                    <a
                        href="/membership-plans"
                        class="inline-flex items-center justify-center rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Plan' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
