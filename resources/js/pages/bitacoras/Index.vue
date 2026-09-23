<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import ActionsDropdown from '@/components/ActionsDropdown.vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import ConfirmCloseDialog from '@/components/ConfirmCloseDialog.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Plus,
    Search,
    ClipboardList,
    Calendar,
    Building2,
    DollarSign,
    Users,
    Sun,
    Lock,
    Archive,
    Receipt,
    ChevronDown,
    ChevronUp,
    Eye,
    Edit,
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
    is_closed?: boolean;
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
    canCreate: boolean;
    isAdmin?: boolean;
    kpis?: {
        total_folios?: number;
        total_bitacoras: number;
        total_payroll: number;
        total_expenses: number;
        total_cost: number;
    };
}>();

const page = usePage();
const isAdmin = computed(() => {
    if (props.isAdmin !== undefined) return props.isAdmin;
    const user = page.props.auth?.user as any;
    return !!(user?.isAdmin || user?.is_admin || (Array.isArray(user?.roles) && user.roles.some((r: any) => (r.name || r) === 'admin')));
});

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

const showDeleteDialog = ref(false);
const selectedBitacora = ref<{ id: number; folio_number: string } | null>(null);

const showCloseDialog = ref(false);
const selectedCloseFolio = ref<GroupedFolio | null>(null);
const isClosing = ref(false);

const handleSearch = () => {
    router.get('/bitacoras', {
        search: search.value,
        branch_id: branchId.value,
        client_id: clientId.value,
        start_date: startDate.value,
        end_date: endDate.value,
        per_page: perPage.value,
    }, { preserveState: true, replace: true });
};

const openDeleteConfirm = (b: { id: number; folio_number: string }) => {
    selectedBitacora.value = b;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (selectedBitacora.value) {
        router.delete(`/bitacoras/${selectedBitacora.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                selectedBitacora.value = null;
            },
        });
    }
};

const openCloseConfirm = (folio: GroupedFolio) => {
    selectedCloseFolio.value = folio;
    showCloseDialog.value = true;
};

const confirmClose = () => {
    if (selectedCloseFolio.value) {
        isClosing.value = true;
        router.post(`/bitacoras/${selectedCloseFolio.value.id}/close`, {}, {
            onSuccess: () => {
                showCloseDialog.value = false;
                selectedCloseFolio.value = null;
                isClosing.value = false;
            },
            onError: () => {
                isClosing.value = false;
            },
        });
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

const formatCurrency = (val?: number) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(val || 0);
};
</script>

<template>
    <Head title="Bitácoras Activas por Folio" />

    <div class="p-3 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full min-w-0">
        <!-- Header with View Switcher Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-4 sm:p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <FolderKanban class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Bitácoras Operativas Agrupadas por Folio
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ isAdmin ? 'Control de folios abiertos, sumatorias acumuladas de nómina y gastos operativos.' : 'Control y seguimiento de bitácoras operativas por folio.' }}
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- Tabs Active / Finalized -->
                <div class="flex items-center gap-1.5 bg-zinc-100 dark:bg-zinc-800/80 p-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-xs flex items-center gap-1.5">
                        <ClipboardList class="h-4 w-4 text-indigo-600" />
                        <span>Folios Activos ({{ kpis?.total_folios ?? bitacoras.total ?? 0 }})</span>
                    </div>
                    <Link
                        href="/bitacoras-finalizadas"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition flex items-center gap-1.5"
                    >
                        <Archive class="h-4 w-4 text-zinc-400" />
                        <span>Folios Finalizados</span>
                    </Link>
                </div>

                <div v-if="canCreate">
                    <Link href="/bitacoras/create">
                        <Button class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow w-full sm:w-auto">
                            <Plus class="h-4 w-4 mr-2" /> Nueva Bitácora
                        </Button>
                    </Link>
                </div>
            </div>
        </div>

        <!-- KPI Summary Cards (Active Folios) - Admin Only -->
        <div v-if="isAdmin && kpis" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-3">
                <div class="p-3 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl">
                    <FolderKanban class="h-5 w-5" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Total Folios Activos</span>
                    <span class="text-lg font-bold text-zinc-900 dark:text-zinc-100 font-mono">{{ kpis.total_folios ?? kpis.total_bitacoras }}</span>
                    <span class="text-[11px] text-zinc-400">({{ kpis.total_bitacoras }} bitácoras registradas)</span>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-3">
                <div class="p-3 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-xl">
                    <Users class="h-5 w-5" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Nómina Total de Folios</span>
                    <span class="text-lg font-bold text-blue-700 dark:text-blue-400 font-mono">{{ formatCurrency(kpis.total_payroll) }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center gap-3">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <Receipt class="h-5 w-5" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Gastos Operativos Totales</span>
                    <span class="text-lg font-bold text-emerald-700 dark:text-emerald-400 font-mono">{{ formatCurrency(kpis.total_expenses) }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-indigo-200 dark:border-indigo-900/50 bg-gradient-to-br from-white to-indigo-50/40 dark:from-zinc-900 dark:to-indigo-950/20 shadow-xs flex items-center gap-3">
                <div class="p-3 bg-indigo-600 text-white rounded-xl shadow-xs">
                    <DollarSign class="h-5 w-5" />
                </div>
                <div>
                    <span class="text-xs text-indigo-800 dark:text-indigo-400 font-semibold block">Gran Total (Nómina + Gastos)</span>
                    <span class="text-lg font-bold text-indigo-700 dark:text-indigo-300 font-mono">{{ formatCurrency(kpis.total_cost) }}</span>
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

        <!-- Bitacoras Container Grouped By Folio -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm w-full min-w-0">
            <!-- Mobile View: Card List (md:hidden) -->
            <div class="block md:hidden divide-y divide-zinc-200 dark:divide-zinc-800">
                <div
                    v-for="folio in bitacoras.data"
                    :key="folio.folio_number"
                    class="p-4 space-y-3 transition"
                    :class="hasSundayInDates(folio.dates)
                        ? 'bg-amber-50/50 dark:bg-amber-950/20'
                        : 'hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40'"
                >
                    <!-- Top Row: Close Button FIRST, then Folio and Dates Count -->
                    <div class="flex items-center justify-between gap-2">
                        <!-- Botón de Cerrar Folio en la primera posición -->
                        <Button
                            size="sm"
                            class="bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs h-8 px-3 rounded-xl shadow-xs flex items-center gap-1"
                            @click="openCloseConfirm(folio)"
                        >
                            <Lock class="h-3.5 w-3.5" />
                            <span>Cerrar Folio</span>
                        </Button>

                        <div class="text-right">
                            <Link :href="`/bitacoras/${folio.id}`" class="text-base font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ folio.folio_number }}
                            </Link>
                            <Badge v-if="folio.dates_count > 1" class="bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 text-[10px] py-0 px-1 font-semibold ml-1">
                                {{ folio.dates_count }} días
                            </Badge>
                        </div>
                    </div>

                    <!-- Client & Branch Details -->
                    <div class="text-xs space-y-1 bg-zinc-50 dark:bg-zinc-800/50 p-2.5 rounded-xl border border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Cliente:</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100 text-right">{{ folio.client?.name || 'Cliente General' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Sucursal Cliente:</span>
                            <span class="text-zinc-700 dark:text-zinc-300 text-right">{{ folio.client_branch?.name || folio.clientBranch?.name || 'Matriz / General' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Sucursal ICC:</span>
                            <span class="text-zinc-700 dark:text-zinc-300 text-right">{{ folio.branch?.name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Encargado:</span>
                            <span class="text-zinc-700 dark:text-zinc-300 text-right">{{ folio.user?.name || 'Sistema' }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-zinc-200/50 dark:border-zinc-700/50">
                            <span class="text-zinc-500 font-medium">Fechas:</span>
                            <span class="font-mono text-zinc-700 dark:text-zinc-300 text-right">{{ folio.dates.join(', ') }}</span>
                        </div>
                    </div>

                    <!-- Folio Summations Row -->
                    <div class="grid grid-cols-3 gap-2 pt-1 text-center text-xs">
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <span class="text-[10px] text-zinc-400 uppercase font-semibold block">Nómina Folio</span>
                            <span class="font-mono font-bold text-blue-700 dark:text-blue-400">{{ formatCurrency(folio.total_payroll) }}</span>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <span class="text-[10px] text-zinc-400 uppercase font-semibold block">Gastos Folio</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(folio.total_expenses) }}</span>
                        </div>
                        <div class="bg-indigo-50 dark:bg-indigo-950/40 p-2 rounded-xl border border-indigo-200 dark:border-indigo-900/50">
                            <span class="text-[10px] text-indigo-700 dark:text-indigo-400 uppercase font-bold block">Total Gasto Folio</span>
                            <span class="font-mono font-bold text-indigo-700 dark:text-indigo-300">{{ formatCurrency(folio.total_cost) }}</span>
                        </div>
                    </div>

                    <!-- Mobile Action: Agregar Actividad al Folio -->
                    <div class="pt-1">
                        <Link :href="`/bitacoras/create?from_folio=${encodeURIComponent(folio.folio_number)}`" class="block">
                            <Button size="sm" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs h-8 rounded-xl shadow-xs flex items-center justify-center gap-1.5">
                                <Plus class="h-3.5 w-3.5" />
                                <span>Agregar Actividad</span>
                            </Button>
                        </Link>
                    </div>

                    <!-- Expand/Collapse Daily Breakdown for Folio -->
                    <div class="pt-1">
                        <Button
                            variant="ghost"
                            size="sm"
                            class="w-full text-xs h-7 text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 justify-between"
                            @click="toggleFolioExpand(folio.folio_number)"
                        >
                            <span>{{ expandedFolioNumbers.includes(folio.folio_number) ? 'Ocultar fechas del folio' : `Ver ${folio.dates_count} fecha(s) detalladas` }}</span>
                            <component :is="expandedFolioNumbers.includes(folio.folio_number) ? ChevronUp : ChevronDown" class="h-3.5 w-3.5" />
                        </Button>

                        <!-- Expanded Sub-Bitácoras -->
                        <div v-if="expandedFolioNumbers.includes(folio.folio_number)" class="mt-2 space-y-2 pl-2 border-l-2 border-indigo-300 dark:border-indigo-700">
                            <div
                                v-for="daily in folio.bitacoras"
                                :key="daily.id"
                                class="p-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 space-y-1.5 text-xs"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ daily.date }}</span>
                                    <div class="flex items-center gap-1.5">
                                        <Link :href="`/bitacoras/${daily.id}`">
                                            <Button size="sm" variant="outline" class="h-6 px-2 text-[10px]">
                                                <Eye class="h-3 w-3 mr-0.5" /> Ver
                                            </Button>
                                        </Link>
                                        <Link :href="`/bitacoras/${daily.id}/edit`">
                                            <Button size="sm" variant="outline" class="h-6 px-2 text-[10px]">
                                                <Edit class="h-3 w-3 mr-0.5" /> Editar
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-1 text-[11px] pt-1 text-zinc-600 dark:text-zinc-400">
                                    <div>Actividades: <strong>{{ daily.activities_count }}</strong></div>
                                    <div>Nómina: <strong>{{ formatCurrency(daily.total_payroll) }}</strong></div>
                                    <div>Gastos: <strong>{{ formatCurrency(daily.total_expenses) }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="bitacoras.data.length === 0" class="py-12 text-center text-zinc-400 italic text-xs">
                    No se encontraron folios activos para los filtros seleccionados.
                </div>
            </div>

            <!-- Desktop View: Table Grouped By Folio (md:block) -->
            <div class="hidden md:block overflow-x-auto w-full">
                <table class="w-full text-left text-xs text-zinc-600 dark:text-zinc-300 min-w-[950px]">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <!-- Botón de Cerrar en la PRIMERA posición de la tabla -->
                            <th class="py-3 px-3 text-center w-32 bg-amber-50/50 dark:bg-amber-950/20 text-amber-900 dark:text-amber-300">
                                Cierre
                            </th>
                            <th class="py-3 px-4">Folio</th>
                            <th class="py-3 px-4">Fechas Registradas</th>
                            <th class="py-3 px-4">Cliente / Sucursal</th>
                            <th class="py-3 px-4">Sucursal ICC</th>
                            <th class="py-3 px-4">Encargado</th>
                            <th class="py-3 px-4 text-center">Actividades</th>
                            <th class="py-3 px-4 text-right">Suma Nómina</th>
                            <th class="py-3 px-4 text-right">Suma Gastos</th>
                            <th class="py-3 px-4 text-right bg-indigo-50/40 dark:bg-indigo-950/20">Total Gasto (Folio)</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <template v-for="folio in bitacoras.data" :key="folio.folio_number">
                            <!-- Main Folio Row -->
                            <tr
                                class="transition"
                                :class="hasSundayInDates(folio.dates)
                                    ? 'bg-amber-50/40 dark:bg-amber-950/20 hover:bg-amber-100/50 dark:hover:bg-amber-900/30'
                                    : 'hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40'"
                            >
                                <!-- Columna 1: Botón de Cerrar Folio en la PRIMERA posición -->
                                <td class="py-3.5 px-3 text-center whitespace-nowrap bg-amber-50/30 dark:bg-amber-950/10">
                                    <Button
                                        size="sm"
                                        class="bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs h-8 px-2.5 rounded-xl shadow-xs inline-flex items-center gap-1 transition active:scale-95"
                                        title="Cerrar y Finalizar Folio"
                                        @click="openCloseConfirm(folio)"
                                    >
                                        <Lock class="h-3.5 w-3.5" />
                                        <span>Cerrar</span>
                                    </Button>
                                </td>

                                <!-- Folio Number with Expand Chevron -->
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
                                        <Badge v-if="folio.dates_count > 1" class="bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 text-[10px] py-0 px-1 font-semibold">
                                            {{ folio.dates_count }} fechas
                                        </Badge>
                                    </div>
                                </td>

                                <!-- Fechas Registradas del Folio -->
                                <td class="py-3.5 px-4 font-mono whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <Calendar class="h-3.5 w-3.5 text-zinc-400" />
                                        <span v-if="folio.dates_count === 1">{{ folio.dates[0] }}</span>
                                        <span v-else class="text-[11px]">{{ folio.dates[0] }} al {{ folio.dates[folio.dates_count - 1] }}</span>
                                        <Badge v-if="hasSundayInDates(folio.dates)" class="bg-amber-500 text-white text-[10px] py-0 px-1 gap-0.5">
                                            <Sun class="h-3 w-3" /> Domingo
                                        </Badge>
                                    </div>
                                </td>

                                <!-- Client -->
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100 block">
                                        {{ folio.client?.name || 'Cliente General' }}
                                    </span>
                                    <span class="text-[11px] text-zinc-400 block">
                                        {{ folio.client_branch?.name || folio.clientBranch?.name || 'Matriz / General' }}
                                    </span>
                                </td>

                                <!-- Branch ICC -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ folio.branch?.name }}
                                    </span>
                                </td>

                                <!-- Encargado -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-zinc-600 dark:text-zinc-400">
                                    {{ folio.user?.name || 'Sistema' }}
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
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-indigo-700 dark:text-indigo-300 whitespace-nowrap bg-indigo-50/40 dark:bg-indigo-950/20">
                                    {{ formatCurrency(folio.total_cost) }}
                                </td>

                                <!-- Acciones / Agregar Actividad y menú desplegable -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="`/bitacoras/create?from_folio=${encodeURIComponent(folio.folio_number)}`">
                                            <Button
                                                size="sm"
                                                class="h-8 px-2.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-xs transition active:scale-95 flex items-center gap-1"
                                                title="Agregar nueva fecha y actividad a este folio"
                                            >
                                                <Plus class="h-3.5 w-3.5" />
                                                <span>Agregar Actividad</span>
                                            </Button>
                                        </Link>

                                        <ActionsDropdown
                                            :actions="[
                                                { label: 'Agregar Actividad', icon: 'edit', onClick: () => router.get(`/bitacoras/create?from_folio=${encodeURIComponent(folio.folio_number)}`) },
                                                { label: 'Ver Detalle Folio', icon: 'view', onClick: () => router.get(`/bitacoras/${folio.id}`) },
                                                { label: 'Editar Última Fecha', icon: 'edit', onClick: () => router.get(`/bitacoras/${folio.id}/edit`) },
                                                { label: 'Cerrar Folio', icon: 'view', onClick: () => openCloseConfirm(folio) },
                                                { label: 'Eliminar Registro', icon: 'delete', variant: 'destructive', onClick: () => openDeleteConfirm(folio) }
                                            ]"
                                        />
                                    </div>
                                </td>
                            </tr>

                            <!-- Expanded Sub-Table: Desglose por Fechas del Folio -->
                            <tr v-if="expandedFolioNumbers.includes(folio.folio_number)" class="bg-zinc-50/80 dark:bg-zinc-800/50">
                                <td colspan="11" class="p-3 sm:px-8 sm:py-4">
                                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3 space-y-2 shadow-xs">
                                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2">
                                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 uppercase tracking-wide flex items-center gap-1.5">
                                                <Calendar class="h-3.5 w-3.5 text-indigo-600" />
                                                Desglose de Bitácoras por Fecha del Folio {{ folio.folio_number }} ({{ folio.dates_count }} días)
                                            </span>
                                            <Link href="/bitacoras/create">
                                                <Button size="sm" variant="ghost" class="h-6 text-[11px] text-indigo-600 hover:text-indigo-800">
                                                    <Plus class="h-3 w-3 mr-1" /> Registrar otra fecha para este folio
                                                </Button>
                                            </Link>
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
                                                    <th class="py-1.5 px-3 text-right">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                                <tr v-for="bItem in folio.bitacoras" :key="bItem.id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                                    <td class="py-2 px-3 font-mono font-semibold text-zinc-900 dark:text-zinc-100">
                                                        {{ bItem.date }}
                                                        <Badge v-if="isSundayDate(bItem.date)" class="bg-amber-500 text-white text-[9px] py-0 px-1 ml-1.5">
                                                            Domingo
                                                        </Badge>
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
                                                        <div class="flex items-center justify-end gap-1">
                                                            <Link :href="`/bitacoras/${bItem.id}`">
                                                                <Button size="sm" variant="outline" class="h-6 px-2 text-[10px]">
                                                                    Ver
                                                                </Button>
                                                            </Link>
                                                            <Link :href="`/bitacoras/${bItem.id}/edit`">
                                                                <Button size="sm" variant="outline" class="h-6 px-2 text-[10px]">
                                                                    Editar
                                                                </Button>
                                                            </Link>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-if="bitacoras.data.length === 0">
                            <td colspan="11" class="py-12 text-center text-zinc-400 italic">
                                No se encontraron folios activos para los filtros seleccionados.
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

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
        :open="showDeleteDialog"
        @update:open="showDeleteDialog = $event"
        title="¿Eliminar Bitácora?"
        :description="`¿Estás seguro de enviar el registro del Folio '${selectedBitacora?.folio_number}' a la papelera (SoftDelete)?`"
        @confirm="confirmDelete"
    />

    <!-- Close Confirmation Dialog -->
    <ConfirmCloseDialog
        :open="showCloseDialog"
        :loading="isClosing"
        @update:open="showCloseDialog = $event"
        :title="`¿Cerrar y Finalizar Folio '${selectedCloseFolio?.folio_number}'?`"
        :description="`Al cerrar el folio '${selectedCloseFolio?.folio_number}' (${selectedCloseFolio?.dates_count || 1} fecha(s) asociadas), se trasladará de inmediato a Folios Finalizados y quedará completamente bloqueado contra cualquier modificación o nuevo registro.`"
        @confirm="confirmClose"
    />
</template>
