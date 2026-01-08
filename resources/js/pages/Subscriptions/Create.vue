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

interface Props {
    member: Member;
    plans: MembershipPlan[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Members', href: '/members' },
    { title: props.member.name, href: `/members/${props.member.id}` },
    {
        title: 'New Subscription',
        href: `/members/${props.member.id}/subscriptions/create`,
    },
];

const form = useForm({
    member_id: props.member.id,
    membership_plan_id: props.plans.length > 0 ? props.plans[0].id : '',
    start_date: new Date().toISOString().split('T')[0],
    auto_renew: false,
});

const selectedPlan = () => {
    return props.plans.find((p) => p.id === form.membership_plan_id);
};

const formatCurrency = (amount: number, currency: string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount / 100);
};

const formatDuration = (days: number) => {
    if (days === 7) return '1 week';
    if (days === 30) return '1 month';
    if (days === 90) return '3 months';
    if (days === 365) return '1 year';
    return `${days} days`;
};

const submit = () => {
    form.post('/subscriptions');
};
</script>

<template>
    <Head title="Create Subscription" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Create Subscription
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Assign a membership plan to {{ member.name }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 text-lg font-semibold">
                        Member Information
                    </h2>
                    <dl class="space-y-2">
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Name
                            </dt>
                            <dd class="mt-1 text-sm">{{ member.name }}</dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Member ID
                            </dt>
                            <dd class="mt-1 text-sm">{{ member.member_id }}</dd>
                        </div>
                        <div>
                            <dt
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Email
                            </dt>
                            <dd class="mt-1 text-sm">{{ member.email }}</dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h2 class="mb-4 text-lg font-semibold">
                        Subscription Details
                    </h2>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label
                                for="membership_plan_id"
                                class="mb-2 block text-sm font-medium"
                                >Membership Plan *</label
                            >
                            <select
                                id="membership_plan_id"
                                v-model="form.membership_plan_id"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500':
                                        form.errors.membership_plan_id,
                                }"
                            >
                                <option
                                    v-for="plan in plans"
                                    :key="plan.id"
                                    :value="plan.id"
                                >
                                    {{ plan.name }} -
                                    {{
                                        formatCurrency(
                                            plan.price,
                                            plan.currency,
                                        )
                                    }}
                                    /
                                    {{ formatDuration(plan.duration_in_days) }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.membership_plan_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.membership_plan_id }}
                            </p>
                        </div>

                        <div
                            v-if="selectedPlan()"
                            class="rounded-lg bg-sidebar p-4 md:col-span-2"
                        >
                            <h3 class="mb-2 text-sm font-medium">
                                Plan Details
                            </h3>
                            <dl class="space-y-2 text-sm">
                                <div>
                                    <dt class="text-muted-foreground">Price</dt>
                                    <dd class="font-medium">
                                        {{
                                            formatCurrency(
                                                selectedPlan()!.price,
                                                selectedPlan()!.currency,
                                            )
                                        }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-muted-foreground">
                                        Duration
                                    </dt>
                                    <dd>
                                        {{
                                            formatDuration(
                                                selectedPlan()!
                                                    .duration_in_days,
                                            )
                                        }}
                                    </dd>
                                </div>
                                <div v-if="selectedPlan()!.description">
                                    <dt class="text-muted-foreground">
                                        Description
                                    </dt>
                                    <dd>{{ selectedPlan()!.description }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <label
                                for="start_date"
                                class="mb-2 block text-sm font-medium"
                                >Start Date *</label
                            >
                            <input
                                id="start_date"
                                v-model="form.start_date"
                                type="date"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                :class="{
                                    'border-red-500': form.errors.start_date,
                                }"
                            />
                            <p
                                v-if="form.errors.start_date"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.start_date }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium"
                                >Auto-Renew</label
                            >
                            <div class="flex items-center gap-2">
                                <input
                                    id="auto_renew"
                                    v-model="form.auto_renew"
                                    type="checkbox"
                                    class="rounded border-input"
                                />
                                <label for="auto_renew" class="text-sm"
                                    >Automatically renew when expired</label
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <a
                        :href="`/members/${member.id}`"
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
                            form.processing
                                ? 'Creating...'
                                : 'Create Subscription'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
