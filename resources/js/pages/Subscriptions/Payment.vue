<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Calendar,
    CreditCard,
    DollarSign,
    FileText,
    User,
    Wallet,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Subscription {
    id: number;
    start_date: string;
    end_date: string;
    status: string;
    member: {
        id: number;
        name: string;
        member_id: string;
        email: string;
        phone: string;
    };
    membership_plan: {
        id: number;
        name: string;
        description: string | null;
        price: number;
        currency: string;
        duration_in_days: number;
    };
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
    available_gateways: string[];
}

const props = defineProps<Props>();
const page = usePage();

const selectedGateway = ref<string>('');
const processing = ref(false);

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
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
    }).format(amount / 100);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

const getGatewayName = (gateway: string) => {
    const names: Record<string, string> = {
        paystack: 'Paystack',
        flutterwave: 'Flutterwave',
        stripe: 'Stripe',
    };
    return names[gateway] || gateway;
};

const processOnlinePayment = () => {
    if (!selectedGateway.value) {
        return;
    }

    processing.value = true;

    router.post(
        `/subscriptions/${props.subscription.id}/payment/online`,
        {
            gateway: selectedGateway.value,
        },
        {
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};

const canRecordManualPayment = () => {
    const userPermissions = page.props.auth?.user?.permissions || [];
    return userPermissions.includes('payments.create');
};
</script>

<template>
    <Head :title="`Payment - ${subscription.member.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto max-w-5xl space-y-6 px-4 py-8">
            <!-- Page Header -->
            <div class="space-y-2">
                <h1 class="text-3xl font-bold tracking-tight">
                    Subscription Payment
                </h1>
                <p class="text-muted-foreground">
                    Complete payment to activate subscription
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Subscription Details -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CreditCard class="h-5 w-5" />
                            Subscription Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="space-y-1">
                                    <p
                                        class="text-sm font-medium text-muted-foreground"
                                    >
                                        Member
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <User
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        <span class="font-medium">{{
                                            subscription.member.name
                                        }}</span>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        {{ subscription.member.email }}
                                    </p>
                                </div>
                            </div>

                            <div class="border-t pt-3">
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Membership Plan
                                </p>
                                <p class="font-medium">
                                    {{ subscription.membership_plan.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{
                                        subscription.membership_plan
                                            .duration_in_days
                                    }}
                                    days
                                </p>
                            </div>

                            <div class="border-t pt-3">
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Subscription Period
                                </p>
                                <div class="flex items-center gap-2">
                                    <Calendar
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="text-sm">
                                        {{
                                            formatDate(subscription.start_date)
                                        }}
                                        -
                                        {{ formatDate(subscription.end_date) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Invoice Details -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Invoice Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <div>
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Invoice Number
                                </p>
                                <p class="font-mono font-medium">
                                    {{ invoice.invoice_number }}
                                </p>
                            </div>

                            <div class="border-t pt-3">
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Due Date
                                </p>
                                <div class="flex items-center gap-2">
                                    <Calendar
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span>{{
                                        formatDate(invoice.due_date)
                                    }}</span>
                                </div>
                            </div>

                            <div class="space-y-2 border-t pt-3">
                                <div
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="text-muted-foreground"
                                        >Subtotal</span
                                    >
                                    <span>{{
                                        formatCurrency(invoice.subtotal)
                                    }}</span>
                                </div>
                                <div
                                    v-if="invoice.tax_amount > 0"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="text-muted-foreground"
                                        >Tax</span
                                    >
                                    <span>{{
                                        formatCurrency(invoice.tax_amount)
                                    }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between border-t pt-2 text-lg font-bold"
                                >
                                    <span>Total</span>
                                    <div class="flex items-center gap-2">
                                        <DollarSign class="h-5 w-5" />
                                        <span>{{
                                            formatCurrency(invoice.total_amount)
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Payment Options -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Wallet class="h-5 w-5" />
                        Select Payment Method
                    </CardTitle>
                    <CardDescription>
                        Choose your preferred payment gateway to complete the
                        transaction
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="gateway">Payment Gateway</Label>
                            <select
                                id="gateway"
                                v-model="selectedGateway"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                <option value="" disabled>
                                    Select a payment gateway
                                </option>
                                <option
                                    v-for="gateway in available_gateways"
                                    :key="gateway"
                                    :value="gateway"
                                >
                                    {{ getGatewayName(gateway) }}
                                </option>
                            </select>
                        </div>

                        <Button
                            @click="processOnlinePayment"
                            :disabled="!selectedGateway || processing"
                            size="lg"
                            class="w-full"
                        >
                            <CreditCard class="mr-2 h-5 w-5" />
                            {{
                                processing
                                    ? 'Processing...'
                                    : `Pay ${formatCurrency(invoice.total_amount)}`
                            }}
                        </Button>
                    </div>

                    <!-- Manual Payment Option (Staff Only) -->
                    <div v-if="canRecordManualPayment()" class="border-t pt-6">
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium">Staff Actions</h3>
                            <p class="text-sm text-muted-foreground">
                                If payment was received in person, you can
                                record it manually.
                            </p>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="
                                    router.visit(
                                        `/subscriptions/${subscription.id}/payment/manual`,
                                    )
                                "
                            >
                                Record Manual Payment
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Payment Information -->
            <Card
                class="border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-950"
            >
                <CardContent class="pt-6">
                    <div class="space-y-2">
                        <h3 class="font-medium">Payment Information</h3>
                        <ul
                            class="list-disc space-y-1 pl-5 text-sm text-muted-foreground"
                        >
                            <li>
                                Your subscription will be activated immediately
                                after payment
                            </li>
                            <li>You will receive a receipt via email</li>
                            <li>All payments are secure and encrypted</li>
                            <li>
                                Supported payment methods: Card, Mobile Money
                                (MTN, Vodafone, AirtelTigo)
                            </li>
                        </ul>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
