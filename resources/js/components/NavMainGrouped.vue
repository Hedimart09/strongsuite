<script setup lang="ts">
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { type Component } from 'vue';

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

defineProps<{
    groups: NavGroup[];
}>();

const page = usePage();
</script>

<template>
    <div v-for="group in groups" :key="group.label" class="space-y-0">
        <SidebarGroup class="px-2 py-0">
            <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>
            <SidebarMenu>
                <Collapsible
                    v-for="item in group.items"
                    :key="item.title"
                    as-child
                    :default-open="false"
                    class="group/collapsible"
                >
                    <SidebarMenuItem>
                        <!-- Item with submenu -->
                        <template v-if="item.items && item.items.length > 0">
                            <CollapsibleTrigger as-child>
                                <SidebarMenuButton :tooltip="item.title">
                                    <component :is="item.icon" />
                                    <span>{{ item.title }}</span>
                                    <ChevronRight
                                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                    />
                                </SidebarMenuButton>
                            </CollapsibleTrigger>
                            <CollapsibleContent>
                                <SidebarMenuSub>
                                    <SidebarMenuSubItem
                                        v-for="subItem in item.items"
                                        :key="subItem.title"
                                    >
                                        <SidebarMenuSubButton
                                            as-child
                                            :is-active="
                                                urlIsActive(
                                                    subItem.href,
                                                    page.url,
                                                )
                                            "
                                        >
                                            <Link :href="subItem.href">
                                                <span>{{ subItem.title }}</span>
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </template>

                        <!-- Simple item without submenu -->
                        <SidebarMenuButton
                            v-else
                            as-child
                            :is-active="urlIsActive(item.href!, page.url)"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href!">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </Collapsible>
            </SidebarMenu>
        </SidebarGroup>
    </div>
</template>
