<script setup lang="ts">
import NavMainGrouped from '@/components/NavMainGrouped.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as membersIndex } from '@/routes/members';
import { index as membershipPlansIndex } from '@/routes/membership-plans';
import { index as attendanceIndex } from '@/routes/attendance';
import { index as invoicesIndex } from '@/routes/invoices';
import { index as paymentsIndex } from '@/routes/payments';
import { index as financeIndex } from '@/routes/finance';
import { index as staffIndex } from '@/routes/staff';
import { reports } from '@/routes';
import { index as settingsIndex } from '@/routes/settings';
import { Link } from '@inertiajs/vue3';
import { BarChart3, ClipboardList, CreditCard, DollarSign, FileText, LayoutGrid, Settings, UserCog, Users, Wallet } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import type { Component } from 'vue';

interface NavSubItem {
    title: string;
    href: string;
}

interface NavItemWithSub {
    title: string;
    href?: string;
    icon: Component;
    items?: NavSubItem[];
}

interface NavGroup {
    label: string;
    items: NavItemWithSub[];
}

const navGroups: NavGroup[] = [
    {
        label: 'Core Operations',
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: 'Attendance',
                href: attendanceIndex(),
                icon: ClipboardList,
            },
        ],
    },
    {
        label: 'Member Management',
        items: [
            {
                title: 'Members',
                href: membersIndex(),
                icon: Users,
            },
            {
                title: 'Membership Plans',
                href: membershipPlansIndex(),
                icon: CreditCard,
            },
        ],
    },
    {
        label: 'Financial',
        items: [
            {
                title: 'Invoices',
                href: invoicesIndex(),
                icon: FileText,
            },
            {
                title: 'Payments',
                href: paymentsIndex(),
                icon: Wallet,
            },
            {
                title: 'Finance',
                icon: DollarSign,
                items: [
                    {
                        title: 'Overview',
                        href: financeIndex(),
                    },
                    {
                        title: 'Reports',
                        href: reports(),
                    },
                ],
            },
        ],
    },
    {
        label: 'System',
        items: [
            {
                title: 'Staff',
                href: staffIndex(),
                icon: UserCog,
            },
            {
                title: 'Settings',
                href: settingsIndex(),
                icon: Settings,
            },
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMainGrouped :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
