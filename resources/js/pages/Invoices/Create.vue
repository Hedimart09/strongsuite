<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Trash2, Save } from 'lucide-vue-next';
import { ref } from 'vue';

interface Member {
    id: number;
    name: string;
    member_id: string;
}

interface Props {
    members: Member[];
    selected_member_id?: number;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/invoices' },
    { title: 'Create', href: '/invoices/create' },
];

interface LineItem {
    description: string;
    amount: number;
}

const form = useForm({
    member_id: props.selected_member_id || '',
    subscription_id: null,
    items: [
        { description: '', amount: 0 },
    ] as LineItem[],
    tax_rate: 0,
    due_date: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    notes: '',
});

const addLineItem = () => {
    form.items.push({ description: '', amount: 0 });
};

const removeLineItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const calculateSubtotal = () => {
    return form.items.reduce((sum, item) => sum + (Number(item.amount) || 0), 0);
};

const calculateTax = () => {
    const subtotal = calculateSubtotal();
    return Math.round(subtotal * (Number(form.tax_rate) / 100));
};

const calculateTotal = () => {
    return calculateSubtotal() + calculateTax();
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
    }).format(amount / 100);
};

const submit = () => {
    form.post('/invoices', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Invoice" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Create Invoice</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Generate a new invoice for a member
                    </p>
                </div>
                <Link
                    href="/invoices"
                    class="inline-flex items-center gap-2 rounded-lg border border-sidebar-border/70 px-4 py-2 text-sm font-medium hover:bg-accent"
                >
                    Cancel
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Member Selection -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <h2 class="text-lg font-semibold mb-4">Member Information</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="member_id" class="block text-sm font-medium mb-2">
                                Member <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="member_id"
                                v-model="form.member_id"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                :class="{ 'border-red-500': form.errors.member_id }"
                                required
                            >
                                <option value="">Select a member</option>
                                <option v-for="member in members" :key="member.id" :value="member.id">
                                    {{ member.name }} ({{ member.member_id }})
                                </option>
                            </select>
                            <p v-if="form.errors.member_id" class="text-xs text-red-500 mt-1">
                                {{ form.errors.member_id }}
                            </p>
                        </div>
                        <div>
                            <label for="due_date" class="block text-sm font-medium mb-2">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="due_date"
                                v-model="form.due_date"
                                type="date"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                :class="{ 'border-red-500': form.errors.due_date }"
                                required
                            />
                            <p v-if="form.errors.due_date" class="text-xs text-red-500 mt-1">
                                {{ form.errors.due_date }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Line Items</h2>
                        <button
                            type="button"
                            @click="addLineItem"
                            class="inline-flex items-center gap-2 rounded-lg border border-sidebar-border/70 px-3 py-1.5 text-sm font-medium hover:bg-accent"
                        >
                            <Plus class="h-4 w-4" />
                            Add Item
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="flex gap-3 items-start"
                        >
                            <div class="flex-1">
                                <label :for="`description-${index}`" class="block text-sm font-medium mb-2">
                                    Description <span class="text-red-500">*</span>
                                </label>
                                <input
                                    :id="`description-${index}`"
                                    v-model="item.description"
                                    type="text"
                                    placeholder="Service or product description"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    required
                                />
                            </div>
                            <div class="w-40">
                                <label :for="`amount-${index}`" class="block text-sm font-medium mb-2">
                                    Amount (GHS) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    :id="`amount-${index}`"
                                    v-model.number="item.amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    required
                                />
                            </div>
                            <button
                                v-if="form.items.length > 1"
                                type="button"
                                @click="removeLineItem(index)"
                                class="mt-8 p-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="mt-6 pt-6 border-t border-sidebar-border/70 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-muted-foreground">Subtotal</span>
                            <span class="font-semibold">{{ formatCurrency(calculateSubtotal()) }}</span>
                        </div>
                        <div class="flex justify-between items-center gap-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-muted-foreground">Tax</span>
                                <input
                                    v-model.number="form.tax_rate"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    placeholder="0"
                                    class="w-20 rounded-lg border border-input bg-background px-2 py-1 text-sm"
                                />
                                <span class="text-sm text-muted-foreground">%</span>
                            </div>
                            <span class="font-semibold">{{ formatCurrency(calculateTax()) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-lg pt-3 border-t border-sidebar-border/70">
                            <span class="font-semibold">Total</span>
                            <span class="font-bold">{{ formatCurrency(calculateTotal()) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <h2 class="text-lg font-semibold mb-4">Additional Notes</h2>
                    <div>
                        <label for="notes" class="block text-sm font-medium mb-2">Notes (Optional)</label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            placeholder="Add any additional information or payment terms..."
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring resize-none"
                        />
                        <p v-if="form.errors.notes" class="text-xs text-red-500 mt-1">
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <Link
                        href="/invoices"
                        class="inline-flex items-center gap-2 rounded-lg border border-sidebar-border/70 px-6 py-2.5 text-sm font-medium hover:bg-accent"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        <Save class="h-4 w-4" />
                        {{ form.processing ? 'Creating...' : 'Create Invoice' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
