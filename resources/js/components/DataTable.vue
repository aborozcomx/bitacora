<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
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
    ChevronsRight,
    X
} from '@lucide/vue';

export interface ColumnDef {
    key: string;
    label: string;
    sortable?: boolean;
    align?: 'left' | 'center' | 'right';
    width?: string;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from?: number | null;
    to?: number | null;
    links?: any[];
}

const props = withDefaults(defineProps<{
    columns: ColumnDef[];
    data: any[] | { data: any[]; [key: string]: any };
    pagination?: PaginationMeta | null;
    filters?: {
        search?: string;
        per_page?: number;
        [key: string]: any;
    };
    searchPlaceholder?: string;
    defaultPerPage?: number;
}>(), {
    pagination: null,
    searchPlaceholder: 'Buscar en la tabla...',
    defaultPerPage: 10,
});

// Detect server-side pagination and extract paginator meta
const isServerSide = computed(() => {
    if (props.pagination && typeof props.pagination === 'object' && 'current_page' in props.pagination) {
        return true;
    }
    if (props.data && !Array.isArray(props.data) && typeof props.data === 'object' && 'current_page' in props.data) {
        return true;
    }
    return false;
});

const paginator = computed<PaginationMeta | null>(() => {
    if (props.pagination && typeof props.pagination === 'object' && 'current_page' in props.pagination) {
        return props.pagination as PaginationMeta;
    }
    if (props.data && !Array.isArray(props.data) && typeof props.data === 'object' && 'current_page' in props.data) {
        return props.data as unknown as PaginationMeta;
    }
    return null;
});

// Normalized raw records array
const rawData = computed<any[]>(() => {
    if (Array.isArray(props.data)) {
        return props.data;
    }
    if (props.data && Array.isArray(props.data.data)) {
        return props.data.data;
    }
    return [];
});

// Search query state
const initialSearch = () => {
    if (props.filters?.search !== undefined) return props.filters.search;
    if (typeof window !== 'undefined') {
        return new URLSearchParams(window.location.search).get('search') || '';
    }
    return '';
};

const searchQuery = ref(initialSearch());

watch(() => props.filters?.search, (newVal) => {
    if (newVal !== undefined && newVal !== searchQuery.value) {
        searchQuery.value = newVal;
    }
});

// Per page state
const initialPerPage = () => {
    if (paginator.value?.per_page) return Number(paginator.value.per_page);
    if (props.filters?.per_page) return Number(props.filters.per_page);
    if (typeof window !== 'undefined') {
        const param = new URLSearchParams(window.location.search).get('per_page');
        if (param) return Number(param);
    }
    return props.defaultPerPage;
};

const perPage = ref(initialPerPage());

watch(() => paginator.value?.per_page, (newVal) => {
    if (newVal && Number(newVal) !== perPage.value) {
        perPage.value = Number(newVal);
    }
});

// Client-side current page state
const clientCurrentPage = ref(1);

// Active current page
const currentPage = computed(() => {
    if (isServerSide.value && paginator.value) {
        return paginator.value.current_page || 1;
    }
    return clientCurrentPage.value;
});

// Sort state
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

// Client-side filtering
const filteredData = computed(() => {
    if (!searchQuery.value.trim()) return rawData.value;

    const query = searchQuery.value.toLowerCase().trim();
    return rawData.value.filter((item) => {
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

// Client-side sorting
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

// Client-side pagination
const clientPaginatedData = computed(() => {
    const start = (clientCurrentPage.value - 1) * perPage.value;
    return sortedData.value.slice(start, start + perPage.value);
});

// Final display data rows
const displayRows = computed(() => {
    if (isServerSide.value) {
        if (!sortColumn.value) return rawData.value;
        return [...rawData.value].sort((a, b) => {
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
    }
    return clientPaginatedData.value;
});

// Total count and page calculations
const totalRecords = computed(() => {
    if (isServerSide.value && paginator.value) {
        return paginator.value.total ?? 0;
    }
    return sortedData.value.length;
});

const totalPages = computed(() => {
    if (isServerSide.value && paginator.value) {
        return paginator.value.last_page || 1;
    }
    return Math.ceil(sortedData.value.length / perPage.value) || 1;
});

const startItem = computed(() => {
    if (totalRecords.value === 0) return 0;
    if (isServerSide.value && paginator.value) {
        return paginator.value.from ?? 0;
    }
    return (clientCurrentPage.value - 1) * perPage.value + 1;
});

const endItem = computed(() => {
    if (totalRecords.value === 0) return 0;
    if (isServerSide.value && paginator.value) {
        return paginator.value.to ?? 0;
    }
    return Math.min(clientCurrentPage.value * perPage.value, sortedData.value.length);
});

// Server navigation helper
const navigate = (params: Record<string, any>) => {
    if (typeof window === 'undefined') return;

    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);

    Object.entries(params).forEach(([key, val]) => {
        if (val === undefined || val === null || val === '') {
            searchParams.delete(key);
        } else {
            searchParams.set(key, String(val));
        }
    });

    const queryString = searchParams.toString();
    const targetUrl = url.pathname + (queryString ? `?${queryString}` : '');

    router.get(targetUrl, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Search handling
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const onSearchInput = () => {
    if (isServerSide.value) {
        if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            navigate({
                search: searchQuery.value.trim() || undefined,
                page: 1,
                per_page: perPage.value,
            });
        }, 400);
    } else {
        clientCurrentPage.value = 1;
    }
};

const onSearchEnter = () => {
    if (isServerSide.value) {
        if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
        navigate({
            search: searchQuery.value.trim() || undefined,
            page: 1,
            per_page: perPage.value,
        });
    }
};

const clearSearch = () => {
    searchQuery.value = '';
    if (isServerSide.value) {
        if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
        navigate({
            search: undefined,
            page: 1,
            per_page: perPage.value,
        });
    } else {
        clientCurrentPage.value = 1;
    }
};

// Per page selector handling
const onPerPageChange = () => {
    if (isServerSide.value) {
        navigate({
            per_page: perPage.value,
            page: 1,
        });
    } else {
        clientCurrentPage.value = 1;
    }
};

// Page navigation
const goToPage = (page: number) => {
    if (page < 1 || page > totalPages.value) return;

    if (isServerSide.value) {
        navigate({ page });
    } else {
        clientCurrentPage.value = page;
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
                    @input="onSearchInput"
                    @keyup.enter="onSearchEnter"
                    :placeholder="searchPlaceholder"
                    class="pl-9 pr-8 bg-zinc-50 dark:bg-zinc-800/50 border-zinc-200 dark:border-zinc-700 w-full text-xs sm:text-sm"
                />
                <button
                    v-if="searchQuery"
                    type="button"
                    @click="clearSearch"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-0.5 rounded-full transition"
                    title="Limpiar búsqueda"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-2 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 w-full sm:w-auto">
                <span>Mostrar</span>
                <select
                    v-model="perPage"
                    @change="onPerPageChange"
                    class="h-8 sm:h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-2 text-xs sm:text-sm font-semibold text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="15">15</option>
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
                        <template v-for="(row, rowIndex) in displayRows" :key="row.id || rowIndex">
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
                        <tr v-if="displayRows.length === 0">
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
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ totalRecords }}</span> registros
                </div>

                <div class="flex items-center justify-center sm:justify-end gap-1 flex-wrap">
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="currentPage <= 1 || totalRecords === 0"
                        @click="goToPage(1)"
                        title="Primera página"
                    >
                        <ChevronsLeft class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="currentPage <= 1 || totalRecords === 0"
                        @click="goToPage(currentPage - 1)"
                        title="Página anterior"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </Button>

                    <span class="px-3 text-xs font-semibold">
                        Página {{ totalRecords === 0 ? 0 : currentPage }} de {{ totalPages }}
                    </span>

                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="currentPage >= totalPages || totalRecords === 0"
                        @click="goToPage(currentPage + 1)"
                        title="Página siguiente"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="currentPage >= totalPages || totalRecords === 0"
                        @click="goToPage(totalPages)"
                        title="Última página"
                    >
                        <ChevronsRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

