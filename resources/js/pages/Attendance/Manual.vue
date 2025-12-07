<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Member {
    id: number;
    name: string;
    member_id: string;
    photo: string | null;
}

interface Props {
    members: Member[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Attendance', href: '/attendance' },
    { title: 'Manual Check-In', href: '/attendance/manual' },
];

const form = useForm({
    member_id: '',
});

const searchQuery = ref('');

const filteredMembers = computed(() => {
    if (!searchQuery.value) {
        return props.members;
    }
    const query = searchQuery.value.toLowerCase();
    return props.members.filter(
        (member) =>
            member.name.toLowerCase().includes(query) ||
            member.member_id.toLowerCase().includes(query)
    );
});

const selectMember = (memberId: number) => {
    form.member_id = memberId.toString();
    form.post('/attendance/check-in/manual', {
        onSuccess: () => {
            form.reset();
            searchQuery.value = '';
        },
    });
};
</script>

<template>
    <Head title="Manual Check-In" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Manual Check-In</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Select a member to manually check in or check out
                </p>
            </div>

            <div class="max-w-4xl mx-auto w-full">
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="mb-6">
                        <label for="search" class="block text-sm font-medium mb-2">Search Member</label>
                        <input
                            id="search"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by name or member ID..."
                            class="w-full rounded-lg border border-input bg-background px-4 py-3 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>

                    <div class="grid gap-3 max-h-[600px] overflow-y-auto">
                        <button
                            v-for="member in filteredMembers"
                            :key="member.id"
                            @click="selectMember(member.id)"
                            :disabled="form.processing && form.member_id === member.id.toString()"
                            class="flex items-center gap-4 p-4 rounded-lg border border-sidebar-border hover:bg-sidebar/50 transition-colors text-left disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <div
                                v-if="member.photo"
                                class="h-12 w-12 rounded-full overflow-hidden flex-shrink-0"
                            >
                                <img
                                    :src="`/storage/${member.photo}`"
                                    :alt="member.name"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <div
                                v-else
                                class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0"
                            >
                                <span class="text-lg font-medium text-primary">
                                    {{ member.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">{{ member.name }}</div>
                                <div class="text-sm text-muted-foreground">{{ member.member_id }}</div>
                            </div>
                            <div v-if="form.processing && form.member_id === member.id.toString()">
                                <span class="text-sm text-muted-foreground">Processing...</span>
                            </div>
                        </button>

                        <div v-if="filteredMembers.length === 0" class="text-center py-8 text-muted-foreground">
                            No members found
                        </div>
                    </div>

                    <p v-if="form.errors.member_id" class="mt-4 text-sm text-red-600">{{ form.errors.member_id }}</p>
                </div>

                <div class="mt-6 text-center">
                    <a
                        href="/attendance"
                        class="inline-flex items-center justify-center rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        View Attendance Log
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
