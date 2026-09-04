<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { MoreHorizontal, Edit, Trash2, Eye } from '@lucide/vue';

interface ActionItem {
    label: string;
    icon?: string;
    variant?: 'default' | 'destructive';
    onClick: () => void;
}

defineProps<{
    actions: ActionItem[];
}>();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                <MoreHorizontal class="h-4 w-4 text-zinc-500" />
                <span class="sr-only">Abrir menú</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-44 shadow-lg border-zinc-200 dark:border-zinc-800">
            <DropdownMenuItem
                v-for="(action, index) in actions"
                :key="index"
                @click="action.onClick"
                :class="[
                    'flex items-center gap-2 cursor-pointer text-sm font-medium py-2 px-3 transition-colors',
                    action.variant === 'destructive'
                        ? 'text-red-600 focus:bg-red-50 focus:text-red-700 dark:text-red-400 dark:focus:bg-red-950/50'
                        : 'text-zinc-700 focus:bg-zinc-100 focus:text-zinc-900 dark:text-zinc-300 dark:focus:bg-zinc-800'
                ]"
            >
                <Edit v-if="action.icon === 'edit'" class="h-4 w-4" />
                <Trash2 v-else-if="action.icon === 'delete'" class="h-4 w-4" />
                <Eye v-else-if="action.icon === 'view'" class="h-4 w-4" />
                <span>{{ action.label }}</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
