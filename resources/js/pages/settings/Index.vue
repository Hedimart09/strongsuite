<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Globe, DollarSign, Percent, CreditCard, Upload } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    settings: {
        id: number;
        gym_name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        timezone: string;
        currency: string;
        tax_rate: number;
        payment_gateways: string[] | null;
        logo: string | null;
    };
    timezones: Record<string, string>;
    currencies: Record<string, string>;
    paymentGateways: Array<{
        id: string;
        name: string;
        description: string;
        enabled: boolean;
    }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gym Settings', href: '/gym-settings' },
];

const form = useForm({
    gym_name: props.settings.gym_name,
    email: props.settings.email || '',
    phone: props.settings.phone || '',
    address: props.settings.address || '',
    timezone: props.settings.timezone,
    currency: props.settings.currency,
    tax_rate: props.settings.tax_rate,
    payment_gateways: props.settings.payment_gateways || [],
    logo: null as File | null,
});

const logoPreview = ref<string | null>(
    props.settings.logo ? `/storage/${props.settings.logo}` : null
);

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const togglePaymentGateway = (gatewayId: string) => {
    const gateways = form.payment_gateways || [];
    const index = gateways.indexOf(gatewayId);

    if (index > -1) {
        form.payment_gateways = gateways.filter(g => g !== gatewayId);
    } else {
        form.payment_gateways = [...gateways, gatewayId];
    }
};

const submit = () => {
    form.put('/gym-settings', {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Gym Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Gym Settings</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Manage your gym's configuration and preferences
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- General Information -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                    <div class="border-b border-sidebar-border/50 px-6 py-4 bg-sidebar/30">
                        <div class="flex items-center gap-2">
                            <Building2 class="h-5 w-5 text-primary" />
                            <h2 class="text-lg font-semibold">General Information</h2>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Logo Upload -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Gym Logo</label>
                            <div class="flex items-center gap-4">
                                <div
                                    v-if="logoPreview"
                                    class="h-24 w-24 rounded-lg border-2 border-dashed border-input overflow-hidden"
                                >
                                    <img :src="logoPreview" alt="Logo preview" class="h-full w-full object-cover" />
                                </div>
                                <div
                                    v-else
                                    class="h-24 w-24 rounded-lg border-2 border-dashed border-input flex items-center justify-center bg-sidebar/30"
                                >
                                    <Upload class="h-8 w-8 text-muted-foreground" />
                                </div>
                                <div class="flex-1">
                                    <input
                                        type="file"
                                        @change="handleLogoChange"
                                        accept="image/*"
                                        class="block w-full text-sm text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border file:border-input file:text-sm file:font-medium file:bg-background hover:file:bg-sidebar/50"
                                    />
                                    <p class="text-xs text-muted-foreground mt-1">
                                        PNG, JPG or GIF (MAX. 2MB)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium mb-2">Gym Name *</label>
                                <input
                                    v-model="form.gym_name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                />
                                <p v-if="form.errors.gym_name" class="text-xs text-destructive mt-1">
                                    {{ form.errors.gym_name }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                />
                                <p v-if="form.errors.email" class="text-xs text-destructive mt-1">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Phone</label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                />
                                <p v-if="form.errors.phone" class="text-xs text-destructive mt-1">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2">Address</label>
                                <textarea
                                    v-model="form.address"
                                    rows="3"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                ></textarea>
                                <p v-if="form.errors.address" class="text-xs text-destructive mt-1">
                                    {{ form.errors.address }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Regional Settings -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                    <div class="border-b border-sidebar-border/50 px-6 py-4 bg-sidebar/30">
                        <div class="flex items-center gap-2">
                            <Globe class="h-5 w-5 text-primary" />
                            <h2 class="text-lg font-semibold">Regional Settings</h2>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium mb-2">Timezone *</label>
                                <select
                                    v-model="form.timezone"
                                    required
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                    <option v-for="(label, value) in timezones" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.timezone" class="text-xs text-destructive mt-1">
                                    {{ form.errors.timezone }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Currency *</label>
                                <select
                                    v-model="form.currency"
                                    required
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                    <option v-for="(label, code) in currencies" :key="code" :value="code">
                                        {{ label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.currency" class="text-xs text-destructive mt-1">
                                    {{ form.errors.currency }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tax Settings -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                    <div class="border-b border-sidebar-border/50 px-6 py-4 bg-sidebar/30">
                        <div class="flex items-center gap-2">
                            <Percent class="h-5 w-5 text-primary" />
                            <h2 class="text-lg font-semibold">Tax/VAT Settings</h2>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="max-w-md">
                            <label class="block text-sm font-medium mb-2">Tax Rate (%) *</label>
                            <input
                                v-model.number="form.tax_rate"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            />
                            <p class="text-xs text-muted-foreground mt-1">
                                Applied to invoices and membership fees
                            </p>
                            <p v-if="form.errors.tax_rate" class="text-xs text-destructive mt-1">
                                {{ form.errors.tax_rate }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Payment Gateways -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                    <div class="border-b border-sidebar-border/50 px-6 py-4 bg-sidebar/30">
                        <div class="flex items-center gap-2">
                            <CreditCard class="h-5 w-5 text-primary" />
                            <h2 class="text-lg font-semibold">Payment Gateways</h2>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div
                                v-for="gateway in paymentGateways"
                                :key="gateway.id"
                                class="flex items-start gap-3 p-4 rounded-lg border border-sidebar-border/50"
                                :class="gateway.enabled ? '' : 'opacity-50'"
                            >
                                <input
                                    type="checkbox"
                                    :id="`gateway-${gateway.id}`"
                                    :checked="form.payment_gateways?.includes(gateway.id)"
                                    @change="togglePaymentGateway(gateway.id)"
                                    :disabled="!gateway.enabled"
                                    class="mt-1 h-4 w-4 rounded border-input text-primary focus:ring-2 focus:ring-ring"
                                />
                                <div class="flex-1">
                                    <label :for="`gateway-${gateway.id}`" class="block font-medium">
                                        {{ gateway.name }}
                                        <span v-if="!gateway.enabled" class="text-xs text-muted-foreground ml-2">
                                            (Not configured)
                                        </span>
                                    </label>
                                    <p class="text-sm text-muted-foreground mt-0.5">
                                        {{ gateway.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <p v-if="form.errors.payment_gateways" class="text-xs text-destructive mt-2">
                            {{ form.errors.payment_gateways }}
                        </p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-primary px-6 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
