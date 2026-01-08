<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';

interface MembershipPlan {
    id: number;
    name: string;
    description: string | null;
    price: number;
    currency: string;
    duration_in_days: number;
    is_active: boolean;
    features: string[] | null;
}

interface Props {
    plan: MembershipPlan;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Membership Plans', href: '/membership-plans' },
    { title: props.plan.name, href: `/membership-plans/${props.plan.id}` },
    { title: 'Edit', href: `/membership-plans/${props.plan.id}/edit` },
];

const form = useForm({
    name: props.plan.name,
    description: props.plan.description || '',
    price: (props.plan.price / 100).toFixed(2),
    currency: props.plan.currency,
    duration_in_days: props.plan.duration_in_days.toString(),
    is_active: props.plan.is_active,
    features:
        props.plan.features && props.plan.features.length > 0
            ? props.plan.features
            : [''],
});

const addFeature = () => {
    form.features.push('');
};

const removeFeature = (index: number) => {
    form.features.splice(index, 1);
};

const submit = () => {
    const filteredFeatures = form.features.filter((f) => f.trim() !== '');
    form.transform((data) => ({
        ...data,
        features: filteredFeatures.length > 0 ? filteredFeatures : null,
    })).put(`/membership-plans/${props.plan.id}`);
};

const deletePlan = () => {
    if (
        confirm(
            'Are you sure you want to delete this plan? This action cannot be undone.',
        )
    ) {
        router.delete(`/membership-plans/${props.plan.id}`);
    }
};
</script>

<template>
    <Head :title="`Edit ${plan.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Edit Membership Plan
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Update plan details
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 text-lg font-semibold">Plan Details</h2>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label
                                for="name"
                                class="mb-2 block text-sm font-medium"
                                >Plan Name *</label
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

                        <div class="md:col-span-2">
                            <label
                                for="description"
                                class="mb-2 block text-sm font-medium"
                                >Description</label
                            >
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500': form.errors.description,
                                }"
                            ></textarea>
                            <p
                                v-if="form.errors.description"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="price"
                                class="mb-2 block text-sm font-medium"
                                >Price *</label
                            >
                            <input
                                id="price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{ 'border-red-500': form.errors.price }"
                            />
                            <p
                                v-if="form.errors.price"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.price }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="currency"
                                class="mb-2 block text-sm font-medium"
                                >Currency *</label
                            >
                            <select
                                id="currency"
                                v-model="form.currency"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500': form.errors.currency,
                                }"
                            >
                                <option value="GHS">GHS (Ghanaian Cedi)</option>
                                <option value="USD">USD (US Dollar)</option>
                                <option value="EUR">EUR (Euro)</option>
                                <option value="GBP">GBP (British Pound)</option>
                            </select>
                            <p
                                v-if="form.errors.currency"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.currency }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="duration_in_days"
                                class="mb-2 block text-sm font-medium"
                                >Duration (days) *</label
                            >
                            <input
                                id="duration_in_days"
                                v-model="form.duration_in_days"
                                type="number"
                                min="1"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500':
                                        form.errors.duration_in_days,
                                }"
                            />
                            <p class="mt-1 text-xs text-muted-foreground">
                                Common: 7 (week), 30 (month), 90 (3 months), 365
                                (year)
                            </p>
                            <p
                                v-if="form.errors.duration_in_days"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.duration_in_days }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium"
                                >Status</label
                            >
                            <div class="flex items-center gap-2">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="rounded border-input"
                                />
                                <label for="is_active" class="text-sm"
                                    >Active</label
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="mb-4 flex items-center justify-between">
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
                        <div
                            v-for="(feature, index) in form.features"
                            :key="index"
                            class="flex gap-2"
                        >
                            <input
                                v-model="form.features[index]"
                                type="text"
                                placeholder="Feature description"
                                class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
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

                <div class="flex justify-between gap-4">
                    <button
                        type="button"
                        @click="deletePlan"
                        class="inline-flex items-center justify-center rounded-lg border border-red-500 bg-background px-4 py-2 text-sm font-medium text-red-500 ring-offset-background transition-colors hover:bg-red-50 dark:hover:bg-red-950"
                    >
                        Delete Plan
                    </button>
                    <div class="flex gap-4">
                        <a
                            :href="`/membership-plans/${plan.id}`"
                            class="inline-flex items-center justify-center rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
