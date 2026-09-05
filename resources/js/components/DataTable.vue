<script setup lang="ts">
import { ref, computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Search,
    ChevronUp,
    ChevronDown,
    ChevronsUpDown,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight
} from '@lucide/vue';

export interface ColumnDef {
    key: string;
    label: string;
    sortable?: boolean;
    align?: 'left' | 'center' | 'right';
    width?: string;
}

const props = withDefaults(defineProps<{
    columns: ColumnDef[];
    data: any[];
    searchPlaceholder?: string;
    defaultPerPage?: number;
}>(), {
    searchPlaceholder: 'Buscar en la tabla...',
    defaultPerPage: 10,
});

const searchQuery = ref('');
const currentPage = ref(1);
const perPage = ref(props.defaultPerPage);
const sortColumn = ref<string>('');
const sortDirection = ref<'asc' | 'desc'>('asc');

const handleSort = (key: string, sortable?: boolean) => {
    if (!sortable) return;

    if (sortColumn.value === key) {
        if (sortDirection.value === 'asc') {
            sortDirection.value = 'desc';
        } else {
            sortColumn.value = '';
            sortDirection.value = 'asc';
        }
    } else {
        sortColumn.value = key;
        sortDirection.value = 'asc';
    }
};

// Filter data by search query across all keys
const filteredData = computed(() => {
    if (!searchQuery.value.trim()) return props.data;

    const query = searchQuery.value.toLowerCase().trim();
    return props.data.filter((item) => {
        return Object.values(item).some((val) => {
            if (val === null || val === undefined) return false;
            if (typeof val === 'object') {
                return Object.values(val).some(nestedVal =>
                    String(nestedVal).toLowerCase().includes(query)
                );
            }
            return String(val).toLowerCase().includes(query);
        });
    });
});

// Sort filtered data
const sortedData = computed(() => {
    if (!sortColumn.value) return filteredData.value;

    return [...filteredData.value].sort((a, b) => {
        const aVal = a[sortColumn.value] ?? '';
        const bVal = b[sortColumn.value] ?? '';

        let comparison = 0;
        if (typeof aVal === 'number' && typeof bVal === 'number') {
            comparison = aVal - bVal;
        } else {
            comparison = String(aVal).localeCompare(String(bVal));
        }

        return sortDirection.value === 'asc' ? comparison : -comparison;
    });
});

// Paginate data
const totalPages = computed(() => Math.ceil(sortedData.value.length / perPage.value) || 1);

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return sortedData.value.slice(start, start + perPage.value);
});

const startItem = computed(() => {
    if (sortedData.value.length === 0) return 0;
    return (currentPage.value - 1) * perPage.value + 1;
});

const endItem = computed(() => {
    return Math.min(currentPage.value * perPage.value, sortedData.value.length);
});

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};
</script>

<template>
    <div class="space-y-4 w-full min-w-0">
        <!-- Controls Header: Search & Per-Page Selector -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 bg-white dark:bg-zinc-900 p-3 sm:p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
            <div class="relative flex-1 w-full sm:max-w-md">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
                <Input
                    v-model="searchQuery"
                    :placeholder="searchPlaceholder"
                    class="pl-9 bg-zinc-50 dark:bg-zinc-800/50 border-zinc-200 dark:border-zinc-700 w-full text-xs sm:text-sm"
                />
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-2 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 w-full sm:w-auto">
                <span>Mostrar</span>
                <select
                    v-model="perPage"
                    @change="currentPage = 1"
                    class="h-8 sm:h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-2 text-xs sm:text-sm font-semibold text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>
                <span>registros</span>
            </div>
        </div>

        <!-- DataTable Container -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm w-full min-w-0">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-xs sm:text-sm text-zinc-600 dark:text-zinc-300 min-w-[550px]">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-semibold text-zinc-500 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th
                                v-for="col in columns"
                                :key="col.key"
                                @click="handleSort(col.key, col.sortable)"
                                :class="[
                                    'py-3.5 px-4 select-none',
                                    col.sortable ? 'cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 transition' : '',
                                    col.align === 'center' ? 'text-center' : col.align === 'right' ? 'text-right' : 'text-left',
                                    col.width || ''
                                ]"
                            >
                                <div class="flex items-center gap-1.5" :class="{ 'justify-end': col.align === 'right', 'justify-center': col.align === 'center' }">
                                    <span>{{ col.label }}</span>
                                    <template v-if="col.sortable">
                                        <ChevronUp v-if="sortColumn === col.key && sortDirection === 'asc'" class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" />
                                        <ChevronDown v-else-if="sortColumn === col.key && sortDirection === 'desc'" class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" />
                                        <ChevronsUpDown v-else class="h-3.5 w-3.5 text-zinc-400" />
                                    </template>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <template v-for="(row, rowIndex) in paginatedData" :key="row.id || rowIndex">
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                                <td
                                    v-for="col in columns"
                                    :key="col.key"
                                    :class="[
                                        'py-3.5 px-4',
                                        col.align === 'center' ? 'text-center' : col.align === 'right' ? 'text-right' : 'text-left'
                                    ]"
                                >
                                    <!-- Custom slot for cell rendering -->
                                    <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                                        {{ row[col.key] ?? '-' }}
                                    </slot>
                                </td>
                            </tr>
                            <slot name="expanded-row" :row="row"></slot>
                        </template>
                        <tr v-if="paginatedData.length === 0">
                            <td :colspan="columns.length" class="py-10 text-center text-zinc-400">
                                No se encontraron registros que coincidan con la búsqueda.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination Controls -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 sm:p-4 bg-zinc-50/60 dark:bg-zinc-800/40 border-t border-zinc-200 dark:border-zinc-800 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                <div class="text-center sm:text-left">
                    Mostrando <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ startItem }}</span> a
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ endItem }}</span> de
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ sortedData.length }}</span> registros
                </div>

                <div class="flex items-center justify-center sm:justify-end gap-1 flex-wrap">
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="currentPage === 1"
                        @click="goToPage(1)"
                    >
                        <ChevronsLeft class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="currentPage === 1"
                        @click="goToPage(currentPage - 1)"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>

                    <span class="px-3 text-xs font-semibold">
                        Página {{ currentPage }} de {{ totalPages }}
                    </span>

                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="currentPage === totalPages"
                        @click="goToPage(currentPage + 1)"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="currentPage === totalPages"
                        @click="goToPage(totalPages)"
                    >
                        <ChevronsRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
