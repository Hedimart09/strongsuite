<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Props {
    open: boolean;
    memberId?: number;
    subscriptionId?: number;
    defaultAmount?: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = ref({
    amount: props.defaultAmount ? (props.defaultAmount / 100).toFixed(2) : '',
    currency: 'GHS',
    payment_method: '',
    transaction_id: '',
    notes: '',
});

const processing = ref(false);
const errors = ref<Record<string, string>>({});

watch(
    () => props.open,
    (newValue) => {
        if (!newValue) {
            // Reset form when modal closes
            form.value = {
                amount: props.defaultAmount ? (props.defaultAmount / 100).toFixed(2) : '',
                currency: 'GHS',
                payment_method: '',
                transaction_id: '',
                notes: '',
            };
            errors.value = {};
        }
    }
);

const paymentMethods = [
    { value: 'cash', label: 'Cash' },
    { value: 'mobile_money', label: 'Mobile Money' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'cheque', label: 'Cheque' },
    { value: 'other', label: 'Other' },
];

const submitPayment = () => {
    // Clear previous errors
    errors.value = {};

    // Validate form
    if (!form.value.amount || parseFloat(form.value.amount) <= 0) {
        errors.value.amount = 'Amount is required and must be greater than 0';
        return;
    }

    if (!form.value.payment_method) {
        errors.value.payment_method = 'Payment method is required';
        return;
    }

    processing.value = true;

    const data: Record<string, any> = {
        amount: Math.round(parseFloat(form.value.amount) * 100), // Convert to cents
        currency: form.value.currency,
        payment_method: form.value.payment_method,
    };

    if (props.memberId) {
        data.member_id = props.memberId;
    }

    if (props.subscriptionId) {
        data.subscription_id = props.subscriptionId;
    }

    if (form.value.transaction_id) {
        data.transaction_id = form.value.transaction_id;
    }

    if (form.value.notes) {
        data.notes = form.value.notes;
    }

    // Determine the endpoint based on context
    let endpoint = '/payments/manual';
    if (props.subscriptionId) {
        endpoint = `/subscriptions/${props.subscriptionId}/payment/manual`;
    }

    router.post(endpoint, data, {
        onSuccess: () => {
            emit('update:open', false);
        },
        onError: (responseErrors) => {
            errors.value = responseErrors as Record<string, string>;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Record Manual Payment</DialogTitle>
                <DialogDescription>
                    Record a payment received in person (cash, cheque, etc.)
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <!-- Amount -->
                <div class="space-y-2">
                    <Label for="amount">
                        Amount <span class="text-red-500">*</span>
                    </Label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <Input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                :class="{ 'border-red-500': errors.amount }"
                            />
                            <p v-if="errors.amount" class="mt-1 text-sm text-red-500">
                                {{ errors.amount }}
                            </p>
                        </div>
                        <select
                            v-model="form.currency"
                            disabled
                            class="w-24 rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="GHS">GHS</option>
                        </select>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="space-y-2">
                    <Label for="payment_method">
                        Payment Method <span class="text-red-500">*</span>
                    </Label>
                    <select
                        id="payment_method"
                        v-model="form.payment_method"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        :class="{ 'border-red-500': errors.payment_method }"
                    >
                        <option value="" disabled>Select payment method</option>
                        <option
                            v-for="method in paymentMethods"
                            :key="method.value"
                            :value="method.value"
                        >
                            {{ method.label }}
                        </option>
                    </select>
                    <p v-if="errors.payment_method" class="text-sm text-red-500">
                        {{ errors.payment_method }}
                    </p>
                </div>

                <!-- Transaction ID -->
                <div class="space-y-2">
                    <Label for="transaction_id">
                        Transaction ID / Reference
                        <span class="text-xs text-muted-foreground">(Optional)</span>
                    </Label>
                    <Input
                        id="transaction_id"
                        v-model="form.transaction_id"
                        placeholder="e.g., CHQ-123456"
                    />
                </div>

                <!-- Notes -->
                <div class="space-y-2">
                    <Label for="notes">
                        Notes <span class="text-xs text-muted-foreground">(Optional)</span>
                    </Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="3"
                        class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Any additional notes about this payment..."
                    ></textarea>
                </div>
            </div>

            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    @click="emit('update:open', false)"
                    :disabled="processing"
                >
                    Cancel
                </Button>
                <Button type="button" @click="submitPayment" :disabled="processing">
                    {{ processing ? 'Recording...' : 'Record Payment' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
