<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Member Login" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-primary/10 to-accent/10 p-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold tracking-tight">Member Portal</h1>
                <p class="mt-2 text-muted-foreground">Enter your member ID and PIN to continue</p>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-8 shadow-lg">
                <div
                    v-if="status"
                    class="mb-4 text-center text-sm font-medium text-green-600"
                >
                    {{ status }}
                </div>

                <Form
                    action="/member/login"
                    method="post"
                    :reset-on-success="['pin']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-6"
                >
                    <div class="grid gap-2">
                        <Label for="member_id">Member ID</Label>
                        <Input
                            id="member_id"
                            type="text"
                            name="member_id"
                            required
                            autofocus
                            placeholder="MEM-12345678"
                            class="text-base"
                        />
                        <InputError :message="errors.member_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="pin">4-Digit PIN</Label>
                        <Input
                            id="pin"
                            type="password"
                            name="pin"
                            required
                            maxlength="4"
                            pattern="[0-9]{4}"
                            inputmode="numeric"
                            placeholder="****"
                            class="text-center text-2xl tracking-widest"
                        />
                        <InputError :message="errors.pin" />
                        <p class="text-xs text-muted-foreground">
                            Your 4-digit PIN was provided when you registered
                        </p>
                    </div>

                    <Button
                        type="submit"
                        :disabled="processing"
                        class="w-full h-12 text-base"
                    >
                        {{ processing ? 'Logging in...' : 'Log In' }}
                    </Button>
                </Form>

                <div class="mt-6 text-center text-sm text-muted-foreground">
                    <p>Forgot your PIN? Please contact gym staff.</p>
                </div>
            </div>

            <div class="mt-6 text-center text-sm text-muted-foreground">
                <p>Staff member? <a href="/login" class="text-primary hover:underline">Login here</a></p>
            </div>
        </div>
    </div>
</template>
