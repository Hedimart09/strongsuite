<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface Member {
    id: number;
    name: string;
    member_id: string;
    email: string;
}

interface MembershipPlan {
    id: number;
    name: string;
    description: string | null;
    price: number;
    currency: string;
    duration_in_days: number;
}

interface Subscription {
    id: number;
    start_date: string;
    end_date: string;
    status: string;
    member: Member;
    membership_plan: MembershipPlan;
}

interface Invoice {
    id: number;
    invoice_number: string;
    subtotal: number;
    tax_amount: number;
    total_amount: number;
    currency: string;
    status: string;
    due_date: string;
}

interface Props {
    subscription: Subscription;
    invoice: Invoice;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Members', href: '/members' },
    {
        title: props.subscription.member.name,
        href: `/members/${props.subscription.member.id}`,
    },
    {
        title: 'Payment',
        href: `/subscriptions/${props.subscription.id}/payment`,
    },
    {
        title: 'Manual Payment',
        href: `/subscriptions/${props.subscription.id}/payment/manual`,
    },
];

const form = useForm({
    amount: props.invoice.total_amount,
    currency: props.invoice.currency,
    payment_method: 'cash',
    transaction_id: '',
    notes: '',
});

const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount / 100);
};

const submit = () => {
    form.post(`/subscriptions/${props.subscription.id}/payment/manual`);
};
</script>

<template>
    <Head title="Record Manual Payment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Record Manual Payment
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Record a payment that was received in person
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Member & Invoice Info -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 text-lg font-semibold">
                        Payment Information
                    </h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Member
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{ subscription.member.name }}
                            </dd>
                            <dd class="text-xs text-muted-foreground">
                                {{ subscription.member.member_id }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Membership Plan
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{ subscription.membership_plan.name }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Invoice Number
                            </dt>
                            <dd class="mt-1 font-mono text-sm">
                                {{ invoice.invoice_number }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Total Amount
                            </dt>
                            <dd class="mt-1 text-sm font-semibold">
                                {{
                                    formatCurrency(
                                        invoice.total_amount,
                                        invoice.currency,
                                    )
                                }}
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Payment Details Form -->
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 text-lg font-semibold">Payment Details</h2>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label
                                for="payment_method"
                                class="mb-2 block text-sm font-medium"
                                >Payment Method *</label
                            >
                            <select
                                id="payment_method"
                                v-model="form.payment_method"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500':
                                        form.errors.payment_method,
                                }"
                            >
                                <option value="cash">Cash</option>
                                <option value="mobile_money">
                                    Mobile Money
                                </option>
                                <option value="bank_transfer">
                                    Bank Transfer
                                </option>
                            </select>
                            <p
                                v-if="form.errors.payment_method"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.payment_method }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="amount"
                                class="mb-2 block text-sm font-medium"
                                >Amount *</label
                            >
                            <div class="relative">
                                <span
                                    class="absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted-foreground"
                                    >{{ invoice.currency }}</span
                                >
                                <input
                                    id="amount"
                                    v-model.number="form.amount"
                                    type="number"
                                    step="1"
                                    min="100"
                                    required
                                    class="w-full rounded-lg border border-input bg-background py-2 pr-3 pl-14 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    :class="{
                                        'border-red-500': form.errors.amount,
                                    }"
                                />
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Amount in minor units (pesewas/cents). Default:
                                {{
                                    formatCurrency(
                                        invoice.total_amount,
                                        invoice.currency,
                                    )
                                }}
                            </p>
                            <p
                                v-if="form.errors.amount"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.amount }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="transaction_id"
                                class="mb-2 block text-sm font-medium"
                                >Transaction ID</label
                            >
                            <input
                                id="transaction_id"
                                v-model="form.transaction_id"
                                type="text"
                                placeholder="Optional reference number"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500':
                                        form.errors.transaction_id,
                                }"
                            />
                            <p
                                v-if="form.errors.transaction_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.transaction_id }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                for="notes"
                                class="mb-2 block text-sm font-medium"
                                >Notes</label
                            >
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                placeholder="Optional payment notes"
                                maxlength="500"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500': form.errors.notes,
                                }"
                            ></textarea>
                            <p
                                v-if="form.errors.notes"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <a
                        :href="`/subscriptions/${subscription.id}/payment`"
                        class="inline-flex items-center justify-center rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50"
                    >
                        {{
                            form.processing ? 'Recording...' : 'Record Payment'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
