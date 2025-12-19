<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form, Head } from '@inertiajs/vue3';
import { User, Lock, LogIn } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Member Login" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-primary/10 to-accent/10 p-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 mx-auto mb-4">
                    <User class="h-8 w-8 text-primary" />
                </div>
                <h1 class="text-3xl font-bold tracking-tight">Member Portal</h1>
                <p class="mt-2 text-muted-foreground">Enter your credentials to continue</p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Welcome Back</CardTitle>
                    <CardDescription>Sign in to access your member dashboard</CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="status"
                        class="mb-6 p-3 text-center text-sm font-medium text-green-600 bg-green-50 dark:bg-green-900/20 rounded-lg"
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
                            <div class="relative">
                                <User class="absolute left-3 top-3 h-5 w-5 text-muted-foreground" />
                                <Input
                                    id="member_id"
                                    type="text"
                                    name="member_id"
                                    required
                                    autofocus
                                    placeholder="MEM-12345678"
                                    class="text-base pl-10"
                                />
                            </div>
                            <InputError :message="errors.member_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="pin">4-Digit PIN</Label>
                            <div class="relative">
                                <Lock class="absolute left-3 top-3 h-5 w-5 text-muted-foreground" />
                                <Input
                                    id="pin"
                                    type="password"
                                    name="pin"
                                    required
                                    maxlength="4"
                                    pattern="[0-9]{4}"
                                    inputmode="numeric"
                                    placeholder="****"
                                    class="text-center text-2xl tracking-widest pl-10"
                                />
                            </div>
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
                            <LogIn class="h-5 w-5 mr-2" />
                            {{ processing ? 'Logging in...' : 'Log In' }}
                        </Button>
                    </Form>

                    <div class="mt-6 text-center text-sm text-muted-foreground border-t pt-4">
                        <p>Forgot your PIN? Please contact gym staff.</p>
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 text-center text-sm text-muted-foreground">
                <p>Staff member? <a href="/login" class="text-primary hover:underline font-medium">Login here</a></p>
            </div>
        </div>
    </div>
</template>
