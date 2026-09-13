<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Search,
    ClipboardList,
    Calendar,
    Building2,
    Lock,
    ArrowLeft,
    CheckCircle2,
    Eye,
    Receipt,
    Users,
    DollarSign,
    Sun,
    Archive,
    ChevronDown,
    ChevronUp,
    FolderKanban
} from '@lucide/vue';

interface Branch {
    id: number;
    name: string;
}

interface Client {
    id: number;
    name: string;
    code: string;
}

interface DailyBitacoraItem {
    id: number;
    folio_number: string;
    date: string;
    notes: string | null;
    activities_count: number;
    total_payroll: number;
    total_expenses: number;
    total_cost: number;
    branch?: Branch;
    user?: { name: string };
}

interface GroupedFolio {
    id: number;
    folio_number: string;
    folio_prefix?: string;
    folio_consecutive?: string;
    date: string;
    dates: string[];
    dates_count: number;
    notes: string | null;
    is_closed: boolean;
    closed_at: string | null;
    closed_by?: { name: string };
    client?: Client;
    client_branch?: { name: string };
    clientBranch?: { name: string };
    branch?: Branch;
    user?: { name: string };
    total_payroll: number;
    total_expenses: number;
    total_cost: number;
    total_activities: number;
    bitacoras?: DailyBitacoraItem[];
}

const props = defineProps<{
    bitacoras: {
        data: GroupedFolio[];
        links: any[];
        current_page?: number;
        last_page?: number;
        total?: number;
        from?: number | null;
        to?: number | null;
        per_page?: number;
    };
    branches: Branch[];
    clients: Client[];
    filters: {
        search?: string;
        branch_id?: string;
        client_id?: string;
        start_date?: string;
        end_date?: string;
        per_page?: number;
    };
    kpis: {
        total_folios?: number;
        total_bitacoras: number;
        total_payroll: number;
        total_expenses: number;
        total_cost: number;
    };
}>();

const search = ref(props.filters.search || '');
const branchId = ref(props.filters.branch_id || '');
const clientId = ref(props.filters.client_id || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const perPage = ref(props.bitacoras.per_page || props.filters.per_page || 10);

const expandedFolioNumbers = ref<string[]>([]);

const toggleFolioExpand = (folioNumber: string) => {
    if (expandedFolioNumbers.value.includes(folioNumber)) {
        expandedFolioNumbers.value = expandedFolioNumbers.value.filter(f => f !== folioNumber);
    } else {
        expandedFolioNumbers.value.push(folioNumber);
    }
};

const handleSearch = () => {
    router.get('/bitacoras-finalizadas', {
        search: search.value,
        branch_id: branchId.value,
        client_id: clientId.value,
        start_date: startDate.value,
        end_date: endDate.value,
        per_page: perPage.value,
    }, { preserveState: true, replace: true });
};

const formatCurrency = (val?: number) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(val || 0);
};

const formatDate = (val?: string | null) => {
    if (!val) return 'N/A';
    try {
        const d = new Date(val);
        return d.toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' });
    } catch {
        return val;
    }
};

const isSundayDate = (dateStr?: string): boolean => {
    if (!dateStr) return false;
    const d = new Date(dateStr + 'T00:00:00');
    return d.getDay() === 0;
};

const hasSundayInDates = (dates?: string[]): boolean => {
    if (!dates) return false;
    return dates.some(d => isSundayDate(d));
};
</script>

<template>
    <Head title="Folios Finalizados" />

    <div class="p-3 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full min-w-0">
        <!-- Header with Navigation Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-4 sm:p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                        <Lock class="h-3.5 w-3.5 text-zinc-500" /> Historial de Folios Cerrados
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <Archive class="h-6 w-6 text-zinc-700 dark:text-zinc-300" />
                    Folios Finalizados (Agrupados con Sumatorias)
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Consulta de folios concluidos y bloqueados contra edición, con el consolidado total de gastos.
                </p>
            </div>

            <!-- View Switcher Tabs -->
            <div class="flex items-center gap-2 bg-zinc-100 dark:bg-zinc-800/80 p-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <Link
                    href="/bitacoras"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition flex items-center gap-1.5"
                >
                    <ClipboardList class="h-4 w-4" />
                    <span>Folios Activos</span>
                </Link>
                <div class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-xs flex items-center gap-1.5">
                    <Lock class="h-4 w-4 text-amber-500" />
                    <span>Finalizados ({{ kpis.total_folios ?? bitacoras.total ?? 0 }})</span>
                </div>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-3">
                <div class="p-3 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-xl">
                    <Lock class="h-5 w-5" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Folios Finalizados</span>
                    <span class="text-lg font-bold text-zinc-900 dark:text-zinc-100 font-mono">{{ kpis.total_folios ?? kpis.total_bitacoras }}</span>
                    <span class="text-[11px] text-zinc-400">({{ kpis.total_bitacoras }} bitácoras cerradas)</span>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-3">
                <div class="p-3 bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 rounded-xl">
                    <Users class="h-5 w-5" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Total Nómina</span>
                    <span class="text-lg font-bold text-blue-700 dark:text-blue-400 font-mono">{{ formatCurrency(kpis.total_payroll) }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-3">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 rounded-xl">
                    <Receipt class="h-5 w-5" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Total Gastos Operativos</span>
                    <span class="text-lg font-bold text-emerald-700 dark:text-emerald-400 font-mono">{{ formatCurrency(kpis.total_expenses) }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-amber-200 dark:border-amber-900/40 bg-gradient-to-br from-white to-amber-50/40 dark:from-zinc-900 dark:to-amber-950/20 shadow-xs flex items-center gap-3">
                <div class="p-3 bg-amber-500 text-white rounded-xl shadow-xs">
                    <DollarSign class="h-5 w-5" />
                </div>
                <div>
                    <span class="text-xs text-amber-800 dark:text-amber-400 font-semibold block">Gran Total (Nómina + Gastos)</span>
                    <span class="text-lg font-bold text-amber-700 dark:text-amber-300 font-mono">{{ formatCurrency(kpis.total_cost) }}</span>
                </div>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm text-xs">
            <div class="relative sm:col-span-2 lg:col-span-1">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
                <Input
                    v-model="search"
                    @keyup.enter="handleSearch"
                    placeholder="Buscar folio, notas..."
                    class="pl-9 bg-zinc-50 dark:bg-zinc-800/50 text-xs h-9"
                />
            </div>
            <div>
                <select
                    v-model="clientId"
                    @change="handleSearch"
                    class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-3 text-xs"
                >
                    <option value="">Todos los Clientes</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>
            <div>
                <select
                    v-model="branchId"
                    @change="handleSearch"
                    class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-3 text-xs"
                >
                    <option value="">Todas las Sucursales</option>
                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
            </div>
            <div>
                <Input type="date" v-model="startDate" @change="handleSearch" class="bg-zinc-50 dark:bg-zinc-800/50 text-xs h-9" />
            </div>
            <div>
                <Input type="date" v-model="endDate" @change="handleSearch" class="bg-zinc-50 dark:bg-zinc-800/50 text-xs h-9" />
            </div>
        </div>

        <!-- Finalized Bitacoras Container Grouped By Folio -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm w-full min-w-0">
            <!-- Mobile View: Cards -->
            <div class="block md:hidden divide-y divide-zinc-200 dark:divide-zinc-800">
                <div
                    v-for="folio in bitacoras.data"
                    :key="folio.folio_number"
                    class="p-4 space-y-3 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition"
                >
                    <!-- Header with Badge and Folio Link -->
                    <div class="flex items-center justify-between gap-2">
                        <Badge class="bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 text-[11px] py-0.5 px-2 font-semibold gap-1">
                            <Lock class="h-3 w-3" /> Folio Finalizado
                        </Badge>
                        <div class="text-right">
                            <Link :href="`/bitacoras/${folio.id}`" class="text-base font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ folio.folio_number }}
                            </Link>
                            <Badge v-if="folio.dates_count > 1" class="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 text-[10px] py-0 px-1 font-semibold ml-1">
                                {{ folio.dates_count }} días
                            </Badge>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="text-xs space-y-1 bg-zinc-50 dark:bg-zinc-800/50 p-2.5 rounded-xl border border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Cliente:</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100 text-right">{{ folio.client?.name || 'Cliente General' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Sucursal:</span>
                            <span class="text-zinc-700 dark:text-zinc-300 text-right">{{ folio.client_branch?.name || folio.clientBranch?.name || 'Matriz' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Cerrado por:</span>
                            <span class="text-zinc-700 dark:text-zinc-300 text-right font-medium text-amber-700 dark:text-amber-400">
                                {{ folio.closed_by?.name || 'Administrador' }}
                            </span>
                        </div>
                        <div v-if="folio.closed_at" class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Fecha Cierre:</span>
                            <span class="text-zinc-600 dark:text-zinc-400 text-right font-mono">{{ formatDate(folio.closed_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-zinc-200/50 dark:border-zinc-700/50">
                            <span class="text-zinc-500 font-medium">Fechas del Folio:</span>
                            <span class="font-mono text-zinc-700 dark:text-zinc-300 text-right">{{ folio.dates.join(', ') }}</span>
                        </div>
                    </div>

                    <!-- Totals Row with Sum of Both Expenses -->
                    <div class="grid grid-cols-3 gap-2 pt-1 text-center text-xs">
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <span class="text-[10px] text-zinc-400 uppercase font-semibold block">Nómina Folio</span>
                            <span class="font-mono font-bold text-blue-700 dark:text-blue-400">{{ formatCurrency(folio.total_payroll) }}</span>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <span class="text-[10px] text-zinc-400 uppercase font-semibold block">Gastos Folio</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(folio.total_expenses) }}</span>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-950/30 p-2 rounded-xl border border-amber-200 dark:border-amber-900/40">
                            <span class="text-[10px] text-amber-700 dark:text-amber-400 uppercase font-bold block">Total Gasto Folio</span>
                            <span class="font-mono font-bold text-amber-700 dark:text-amber-300">{{ formatCurrency(folio.total_cost) }}</span>
                        </div>
                    </div>

                    <!-- Expand button -->
                    <div class="pt-1 flex items-center justify-between gap-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-xs h-7 text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 flex-1 justify-between"
                            @click="toggleFolioExpand(folio.folio_number)"
                        >
                            <span>{{ expandedFolioNumbers.includes(folio.folio_number) ? 'Ocultar desglose' : `Ver ${folio.dates_count} fecha(s)` }}</span>
                            <component :is="expandedFolioNumbers.includes(folio.folio_number) ? ChevronUp : ChevronDown" class="h-3.5 w-3.5" />
                        </Button>

                        <Link :href="`/bitacoras/${folio.id}`">
                            <Button size="sm" variant="outline" class="h-7 px-2.5 text-xs">
                                <Eye class="h-3.5 w-3.5 mr-1" /> Ver Folio
                            </Button>
                        </Link>
                    </div>

                    <!-- Sub-Bitácoras expanded for mobile -->
                    <div v-if="expandedFolioNumbers.includes(folio.folio_number)" class="mt-2 space-y-2 pl-2 border-l-2 border-amber-300 dark:border-amber-700">
                        <div
                            v-for="daily in folio.bitacoras"
                            :key="daily.id"
                            class="p-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 space-y-1.5 text-xs"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ daily.date }}</span>
                                <Link :href="`/bitacoras/${daily.id}`">
                                    <Button size="sm" variant="outline" class="h-6 px-2 text-[10px]">
                                        <Eye class="h-3 w-3 mr-0.5" /> Ver Detalle
                                    </Button>
                                </Link>
                            </div>
                            <div class="grid grid-cols-3 gap-1 text-[11px] pt-1 text-zinc-600 dark:text-zinc-400">
                                <div>Actividades: <strong>{{ daily.activities_count }}</strong></div>
                                <div>Nómina: <strong>{{ formatCurrency(daily.total_payroll) }}</strong></div>
                                <div>Gastos: <strong>{{ formatCurrency(daily.total_expenses) }}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="bitacoras.data.length === 0" class="py-12 text-center text-zinc-400 italic text-xs">
                    No se encontraron folios finalizados para los filtros seleccionados.
                </div>
            </div>

            <!-- Desktop View: Table Grouped By Folio -->
            <div class="hidden md:block overflow-x-auto w-full">
                <table class="w-full text-left text-xs text-zinc-600 dark:text-zinc-300 min-w-[950px]">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-3 text-center w-28">Estado</th>
                            <th class="py-3 px-4">Folio</th>
                            <th class="py-3 px-4">Fechas Registradas</th>
                            <th class="py-3 px-4">Cliente / Sucursal</th>
                            <th class="py-3 px-4">Cerrado Por</th>
                            <th class="py-3 px-4 text-center">Actividades</th>
                            <th class="py-3 px-4 text-right">Suma Nómina</th>
                            <th class="py-3 px-4 text-right">Suma Gastos</th>
                            <th class="py-3 px-4 text-right bg-amber-50/40 dark:bg-amber-950/20">Total Gasto (Folio)</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <template v-for="folio in bitacoras.data" :key="folio.folio_number">
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                                <!-- Estado Cierre (Primera posición) -->
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    <Badge class="bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 text-[10px] py-0.5 px-2 font-semibold gap-0.5">
                                        <Lock class="h-3 w-3" /> Cerrado
                                    </Badge>
                                </td>

                                <!-- Folio con chevron para expandir -->
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="toggleFolioExpand(folio.folio_number)"
                                            class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                                            :title="expandedFolioNumbers.includes(folio.folio_number) ? 'Ocultar desglose' : 'Ver desglose por fecha'"
                                        >
                                            <component :is="expandedFolioNumbers.includes(folio.folio_number) ? ChevronUp : ChevronDown" class="h-4 w-4" />
                                        </button>
                                        <Link :href="`/bitacoras/${folio.id}`" class="hover:underline">
                                            {{ folio.folio_number }}
                                        </Link>
                                        <Badge v-if="folio.dates_count > 1" class="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 text-[10px] py-0 px-1 font-semibold">
                                            {{ folio.dates_count }} fechas
                                        </Badge>
                                    </div>
                                </td>

                                <!-- Fechas -->
                                <td class="py-3.5 px-4 whitespace-nowrap font-mono">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <Calendar class="h-3.5 w-3.5 text-zinc-400" />
                                        <span v-if="folio.dates_count === 1">{{ folio.dates[0] }}</span>
                                        <span v-else class="text-[11px]">{{ folio.dates[0] }} al {{ folio.dates[folio.dates_count - 1] }}</span>
                                    </div>
                                </td>

                                <!-- Cliente -->
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100 block">
                                        {{ folio.client?.name || 'Cliente General' }}
                                    </span>
                                    <span class="text-[11px] text-zinc-400 block">
                                        {{ folio.client_branch?.name || folio.clientBranch?.name || 'Matriz' }}
                                    </span>
                                </td>

                                <!-- Cerrado Por -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-zinc-700 dark:text-zinc-300">
                                    <span class="font-medium text-amber-700 dark:text-amber-400 block">
                                        {{ folio.closed_by?.name || 'Administrador' }}
                                    </span>
                                    <span v-if="folio.closed_at" class="text-[10px] text-zinc-400 block font-mono">
                                        {{ formatDate(folio.closed_at) }}
                                    </span>
                                </td>

                                <!-- Actividades Totales -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap font-mono font-semibold">
                                    <Badge variant="secondary" class="font-mono text-xs">
                                        {{ folio.total_activities }} act.
                                    </Badge>
                                </td>

                                <!-- Suma Nómina Folio -->
                                <td class="py-3.5 px-4 text-right font-mono font-medium text-blue-700 dark:text-blue-400 whitespace-nowrap">
                                    {{ formatCurrency(folio.total_payroll) }}
                                </td>

                                <!-- Suma Gastos Folio -->
                                <td class="py-3.5 px-4 text-right font-mono font-medium text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                    {{ formatCurrency(folio.total_expenses) }}
                                </td>

                                <!-- Total Gasto Folio (Suma de ambos gastos) -->
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-amber-700 dark:text-amber-300 whitespace-nowrap bg-amber-50/40 dark:bg-amber-950/20">
                                    {{ formatCurrency(folio.total_cost) }}
                                </td>

                                <!-- Acción -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <Link :href="`/bitacoras/${folio.id}`">
                                        <Button size="sm" variant="outline" class="h-8 px-2.5 text-xs">
                                            <Eye class="h-3.5 w-3.5 mr-1" /> Ver Detalle
                                        </Button>
                                    </Link>
                                </td>
                            </tr>

                            <!-- Expanded Sub-Table: Desglose por Fechas del Folio Finalizado -->
                            <tr v-if="expandedFolioNumbers.includes(folio.folio_number)" class="bg-zinc-50/80 dark:bg-zinc-800/50">
                                <td colspan="10" class="p-3 sm:px-8 sm:py-4">
                                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3 space-y-2 shadow-xs">
                                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wide flex items-center gap-1.5">
                                                <Calendar class="h-3.5 w-3.5 text-amber-600" />
                                                Bitácoras del Folio Finalizado {{ folio.folio_number }} ({{ folio.dates_count }} fechas)
                                            </span>
                                        </div>

                                        <table class="w-full text-xs text-left">
                                            <thead class="text-[10px] uppercase text-zinc-400 border-b border-zinc-100 dark:border-zinc-800">
                                                <tr>
                                                    <th class="py-1.5 px-3">Fecha</th>
                                                    <th class="py-1.5 px-3">Encargado</th>
                                                    <th class="py-1.5 px-3 text-center">Actividades</th>
                                                    <th class="py-1.5 px-3 text-right">Nómina del Día</th>
                                                    <th class="py-1.5 px-3 text-right">Gastos del Día</th>
                                                    <th class="py-1.5 px-3 text-right">Total del Día</th>
                                                    <th class="py-1.5 px-3 text-right">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                                <tr v-for="bItem in folio.bitacoras" :key="bItem.id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                                    <td class="py-2 px-3 font-mono font-semibold text-zinc-900 dark:text-zinc-100">
                                                        {{ bItem.date }}
                                                    </td>
                                                    <td class="py-2 px-3 text-zinc-600 dark:text-zinc-400">
                                                        {{ bItem.user?.name || folio.user?.name || '-' }}
                                                    </td>
                                                    <td class="py-2 px-3 text-center font-mono">
                                                        {{ bItem.activities_count }}
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono text-blue-700 dark:text-blue-400">
                                                        {{ formatCurrency(bItem.total_payroll) }}
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400">
                                                        {{ formatCurrency(bItem.total_expenses) }}
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono font-bold text-zinc-900 dark:text-zinc-100">
                                                        {{ formatCurrency(bItem.total_cost) }}
                                                    </td>
                                                    <td class="py-2 px-3 text-right">
                                                        <Link :href="`/bitacoras/${bItem.id}`">
                                                            <Button size="sm" variant="outline" class="h-6 px-2 text-[10px]">
                                                                Ver
                                                            </Button>
                                                        </Link>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-if="bitacoras.data.length === 0">
                            <td colspan="10" class="py-12 text-center text-zinc-400 italic">
                                No se encontraron folios finalizados para los filtros seleccionados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination Controls -->
            <div v-if="bitacoras.links && bitacoras.links.length > 3" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 sm:p-4 bg-zinc-50/60 dark:bg-zinc-800/40 border-t border-zinc-200 dark:border-zinc-800 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                <div class="text-center sm:text-left">
                    Mostrando <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ bitacoras.from || 0 }}</span> a
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ bitacoras.to || 0 }}</span> de
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ bitacoras.total || 0 }}</span> folios
                </div>

                <div class="flex items-center justify-center sm:justify-end gap-1 flex-wrap">
                    <Link
                        v-for="(link, i) in bitacoras.links"
                        :key="i"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-2.5 py-1 text-xs rounded-md border transition"
                        :class="[
                            link.active ? 'bg-indigo-600 text-white border-indigo-600 font-bold' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100',
                            !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
