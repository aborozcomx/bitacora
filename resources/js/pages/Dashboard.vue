<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
    FolderKanban,
    Calendar,
    Users,
    DollarSign,
    Receipt,
    Clock,
    Plus,
    Eye,
    Filter,
    RotateCcw,
    Shield,
    UserCheck,
    Briefcase,
    Building2,
    CalendarDays,
    Wallet,
    Lock,
    Sun,
    CheckCircle2,
    AlertCircle,
    ChevronRight,
} from '@lucide/vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

interface ActiveFolio {
    id: number;
    folio_number: string;
    folio_prefix?: string;
    folio_consecutive?: string;
    client?: { id: number; name: string; code: string };
    client_branch?: { id: number; name: string; code: string | null };
    branch?: { id: number; name: string; code: string | null };
    user?: { id: number; name: string; email: string };
    dates: string[];
    dates_count: number;
    total_payroll: number;
    total_expenses: number;
    total_cost: number;
    activities_count: number;
}

interface ExpenseItem {
    id: number;
    concept: string;
    amount: number;
    folio_number?: string;
    bitacora_id?: number;
    client_name?: string;
    payment_method: string;
    payment_card?: string;
    reference_number?: string;
}

interface CalendarExpenseDay {
    date: string;
    day_name: string;
    day_number: string;
    is_today: boolean;
    is_sunday: boolean;
    total_amount: number;
    expenses_count: number;
    expenses: ExpenseItem[];
}

interface WorkerItem {
    id: number;
    employee_id: number;
    employee_name: string;
    employee_code?: string;
    hours_worked: number;
    overtime_hours: number;
    total_hours: number;
    is_absent: boolean;
    is_partial_shift: boolean;
    partial_shift_reason?: string | null;
    folio_number?: string;
    bitacora_id?: number;
    activity_description?: string;
    activity_type?: string;
}

interface CalendarPersonnelDay {
    date: string;
    day_name: string;
    day_number: string;
    is_today: boolean;
    is_sunday: boolean;
    workers_count: number;
    total_hours: number;
    workers: WorkerItem[];
}

interface Props {
    kpis: {
        active_folios_count: number;
        total_period_cost: number;
        total_period_expenses: number;
        total_period_payroll: number;
        total_period_hours: number;
        total_unique_workers: number;
    };
    activeFolios: ActiveFolio[];
    expensesCalendar: CalendarExpenseDay[];
    personnelCalendar: CalendarPersonnelDay[];
    filters: {
        start_date: string;
        end_date: string;
        user_id?: string;
    };
    isAdmin: boolean;
    availableManagers: Array<{ id: number; name: string; email: string }>;
    currentUser: {
        id: number;
        name: string;
        email: string;
    };
}

const props = defineProps<Props>();

const activeTab = ref<'folios' | 'expenses' | 'personnel'>('folios');

const formFilters = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    user_id: props.filters.user_id || '',
});

const applyFilters = () => {
    router.get('/dashboard', {
        start_date: formFilters.value.start_date,
        end_date: formFilters.value.end_date,
        user_id: formFilters.value.user_id || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatCurrency = (val?: number) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(val || 0);
};

// Date Presets
const setThisWeek = () => {
    const now = new Date();
    const day = now.getDay();
    const diffToMon = (day === 0 ? -6 : 1) - day;
    const monday = new Date(now);
    monday.setDate(now.getDate() + diffToMon);
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);

    formFilters.value.start_date = monday.toISOString().substring(0, 10);
    formFilters.value.end_date = sunday.toISOString().substring(0, 10);
    applyFilters();
};

const setLastWeek = () => {
    const now = new Date();
    const day = now.getDay();
    const diffToMon = (day === 0 ? -6 : 1) - day;
    const lastMon = new Date(now);
    lastMon.setDate(now.getDate() + diffToMon - 7);
    const lastSun = new Date(lastMon);
    lastSun.setDate(lastMon.getDate() + 6);

    formFilters.value.start_date = lastMon.toISOString().substring(0, 10);
    formFilters.value.end_date = lastSun.toISOString().substring(0, 10);
    applyFilters();
};

const setThisMonth = () => {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), 1);
    const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);

    formFilters.value.start_date = start.toISOString().substring(0, 10);
    formFilters.value.end_date = end.toISOString().substring(0, 10);
    applyFilters();
};

const resetFilters = () => {
    formFilters.value.user_id = '';
    setThisWeek();
};
</script>

<template>
    <Head title="Panel de Control - Dashboard" />

    <div class="p-3 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full min-w-0">
        <!-- ======================================================== -->
        <!-- HEADER & USER SCOPE BANNER -->
        <!-- ======================================================== -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-zinc-900 p-5 sm:p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <Briefcase class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                        Panel de Control Operativo
                    </h1>
                    <Badge v-if="isAdmin" class="bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 font-semibold text-xs py-0.5 px-2.5 gap-1">
                        <Shield class="h-3.5 w-3.5" /> Modo Administrador
                    </Badge>
                    <Badge v-else class="bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-semibold text-xs py-0.5 px-2.5 gap-1">
                        <UserCheck class="h-3.5 w-3.5" /> Mis Registros: {{ currentUser.name }}
                    </Badge>
                </div>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ isAdmin
                        ? 'Supervisión ejecutiva de folios activos, nómina, gastos operativos y asignación de personal.'
                        : 'Control personalizado de tus folios abiertos, gastos semanales y equipo de trabajo asignado.'
                    }}
                </p>
            </div>

            <!-- Quick Action: Nueva Bitácora -->
            <div class="flex items-center gap-2">
                <Link href="/bitacoras/create">
                    <Button class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm text-xs font-semibold h-9 sm:h-10 px-4 flex items-center gap-1.5">
                        <Plus class="h-4 w-4" />
                        <span>Nueva Bitácora</span>
                    </Button>
                </Link>
                <Link href="/bitacoras">
                    <Button variant="outline" class="rounded-xl text-xs font-medium h-9 sm:h-10 px-3.5">
                        Ver Bitácoras
                    </Button>
                </Link>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- FILTER BAR (CUSTOM DATE RANGE + PRESETS + USER FILTER) -->
        <!-- ======================================================== -->
        <div class="bg-white dark:bg-zinc-900 p-4 sm:p-5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <!-- Date Presets -->
                <div class="flex items-center gap-1.5 flex-wrap text-xs">
                    <span class="text-zinc-400 font-semibold uppercase text-[11px] mr-1">Rango rápido:</span>
                    <Button variant="outline" size="sm" class="h-7 text-xs rounded-lg px-2.5" @click="setThisWeek">
                        Esta Semana
                    </Button>
                    <Button variant="outline" size="sm" class="h-7 text-xs rounded-lg px-2.5" @click="setLastWeek">
                        Semana Anterior
                    </Button>
                    <Button variant="outline" size="sm" class="h-7 text-xs rounded-lg px-2.5" @click="setThisMonth">
                        Este Mes
                    </Button>
                </div>

                <!-- Custom Range Inputs + User Select (if Admin) -->
                <form @submit.prevent="applyFilters" class="flex items-center gap-2 flex-wrap">
                    <!-- Admin User Filter -->
                    <div v-if="isAdmin && availableManagers.length" class="w-48">
                        <select
                            v-model="formFilters.user_id"
                            class="w-full h-8 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 text-xs focus:ring-1 focus:ring-indigo-500"
                            @change="applyFilters"
                        >
                            <option value="">Todos los Encargados</option>
                            <option v-for="m in availableManagers" :key="m.id" :value="String(m.id)">
                                {{ m.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Date Start -->
                    <div class="flex items-center gap-1.5">
                        <Label for="start_date" class="text-xs text-zinc-500 sr-only">Fecha Inicio</Label>
                        <Input
                            id="start_date"
                            type="date"
                            v-model="formFilters.start_date"
                            class="h-8 text-xs w-36 rounded-lg font-mono"
                        />
                    </div>

                    <span class="text-zinc-400 text-xs">al</span>

                    <!-- Date End -->
                    <div class="flex items-center gap-1.5">
                        <Label for="end_date" class="text-xs text-zinc-500 sr-only">Fecha Fin</Label>
                        <Input
                            id="end_date"
                            type="date"
                            v-model="formFilters.end_date"
                            class="h-8 text-xs w-36 rounded-lg font-mono"
                        />
                    </div>

                    <Button type="submit" size="sm" class="bg-indigo-600 hover:bg-indigo-700 text-white h-8 px-3 rounded-lg text-xs font-semibold gap-1">
                        <Filter class="h-3.5 w-3.5" />
                        <span>Filtrar</span>
                    </Button>

                    <Button type="button" variant="ghost" size="sm" class="h-8 px-2 text-xs text-zinc-500 hover:text-zinc-800" title="Restablecer filtros" @click="resetFilters">
                        <RotateCcw class="h-3.5 w-3.5" />
                    </Button>
                </form>
            </div>

            <!-- Active Range Indicator -->
            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                <span class="flex items-center gap-1.5 font-medium">
                    <Calendar class="h-3.5 w-3.5 text-indigo-600" />
                    Periodo consultado: <strong class="text-zinc-800 dark:text-zinc-200 font-mono">{{ filters.start_date }}</strong> al <strong class="text-zinc-800 dark:text-zinc-200 font-mono">{{ filters.end_date }}</strong>
                </span>
                <span v-if="filters.user_id" class="text-indigo-600 font-medium text-[11px]">
                    Filtrado por encargado específico
                </span>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- KPI METRICS SUMMARY (6 CARDS) -->
        <!-- ======================================================== -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <!-- 1. Folios Activos -->
            <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-zinc-500 uppercase">Folios Activos</span>
                    <FolderKanban class="h-4 w-4 text-indigo-600" />
                </div>
                <div class="mt-2">
                    <span class="text-xl font-bold font-mono text-zinc-900 dark:text-zinc-100">{{ kpis.active_folios_count }}</span>
                    <span class="text-[10px] text-zinc-400 block">Abiertos sin cerrar</span>
                </div>
            </div>

            <!-- 2. Gran Total Periodo (Ambos Gastos) -->
            <div class="bg-indigo-50/60 dark:bg-indigo-950/30 p-3.5 rounded-2xl border border-indigo-200 dark:border-indigo-900/50 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 uppercase">Total Gasto</span>
                    <DollarSign class="h-4 w-4 text-indigo-600" />
                </div>
                <div class="mt-2">
                    <span class="text-lg font-bold font-mono text-indigo-800 dark:text-indigo-200">{{ formatCurrency(kpis.total_period_cost) }}</span>
                    <span class="text-[10px] text-indigo-600 dark:text-indigo-400 block">Nómina + Gastos</span>
                </div>
            </div>

            <!-- 3. Gastos Operativos -->
            <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-zinc-500 uppercase">Gastos Operativos</span>
                    <Receipt class="h-4 w-4 text-emerald-600" />
                </div>
                <div class="mt-2">
                    <span class="text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400">{{ formatCurrency(kpis.total_period_expenses) }}</span>
                    <span class="text-[10px] text-zinc-400 block">Compras y viáticos</span>
                </div>
            </div>

            <!-- 4. Nómina Devengada -->
            <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-zinc-500 uppercase">Nómina Personal</span>
                    <Wallet class="h-4 w-4 text-blue-600" />
                </div>
                <div class="mt-2">
                    <span class="text-lg font-bold font-mono text-blue-700 dark:text-blue-400">{{ formatCurrency(kpis.total_period_payroll) }}</span>
                    <span class="text-[10px] text-zinc-400 block">Mano de obra periodo</span>
                </div>
            </div>

            <!-- 5. Personal Activo -->
            <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-zinc-500 uppercase">Personal a Cargo</span>
                    <Users class="h-4 w-4 text-purple-600" />
                </div>
                <div class="mt-2">
                    <span class="text-xl font-bold font-mono text-zinc-900 dark:text-zinc-100">{{ kpis.total_unique_workers }}</span>
                    <span class="text-[10px] text-zinc-400 block">Empleados en periodo</span>
                </div>
            </div>

            <!-- 6. Total Horas Hombre -->
            <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-zinc-500 uppercase">Horas Laboradas</span>
                    <Clock class="h-4 w-4 text-amber-600" />
                </div>
                <div class="mt-2">
                    <span class="text-xl font-bold font-mono text-zinc-900 dark:text-zinc-100">{{ kpis.total_period_hours }} <span class="text-xs font-normal">hrs</span></span>
                    <span class="text-[10px] text-zinc-400 block">Ordinarias y extras</span>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- MAIN CONTENT TABS NAVIGATION -->
        <!-- ======================================================== -->
        <div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-2">
            <button
                type="button"
                @click="activeTab = 'folios'"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2"
                :class="activeTab === 'folios'
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 border border-zinc-200 dark:border-zinc-800'"
            >
                <FolderKanban class="h-4 w-4" />
                <span>Folios Activos</span>
                <Badge class="ml-1 text-[10px] py-0 px-1.5" :class="activeTab === 'folios' ? 'bg-white/20 text-white' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300'">
                    {{ activeFolios.length }}
                </Badge>
            </button>

            <button
                type="button"
                @click="activeTab = 'expenses'"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2"
                :class="activeTab === 'expenses'
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 border border-zinc-200 dark:border-zinc-800'"
            >
                <Receipt class="h-4 w-4" />
                <span>Gastos por Semana (Calendario)</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'personnel'"
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2"
                :class="activeTab === 'personnel'
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 border border-zinc-200 dark:border-zinc-800'"
            >
                <Users class="h-4 w-4" />
                <span>Personal a Cargo (Calendario)</span>
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 1: FOLIOS ACTIVOS -->
        <!-- ======================================================== -->
        <div v-if="activeTab === 'folios'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <FolderKanban class="h-5 w-5 text-indigo-600" />
                        Folios Operativos Activos
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Folios abiertos que admiten captura de nuevas jornadas, actividades y gastos.
                    </p>
                </div>
            </div>

            <!-- Grid of Active Folio Cards -->
            <div v-if="activeFolios.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="folio in activeFolios"
                    :key="folio.folio_number"
                    class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-4 shadow-xs flex flex-col justify-between space-y-3 hover:border-indigo-300 dark:hover:border-indigo-700 transition"
                >
                    <!-- Top Card Header -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <Link :href="`/bitacoras/${folio.id}`" class="text-lg font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ folio.folio_number }}
                            </Link>
                            <span class="text-xs text-zinc-500 block font-medium mt-0.5">
                                {{ folio.client?.name || 'Cliente General' }}
                            </span>
                        </div>
                        <Badge class="bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 text-[10px] py-0.5 px-2 font-semibold">
                            Abierto
                        </Badge>
                    </div>

                    <!-- Details Box -->
                    <div class="bg-zinc-50 dark:bg-zinc-800/50 p-2.5 rounded-xl border border-zinc-100 dark:border-zinc-800 text-xs space-y-1">
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-300">
                            <span class="text-zinc-400">Sucursal Cliente:</span>
                            <span class="font-medium text-right">{{ folio.client_branch?.name || 'Matriz / General' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-300">
                            <span class="text-zinc-400">Sucursal ICC:</span>
                            <span class="font-medium text-right">{{ folio.branch?.name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-300">
                            <span class="text-zinc-400">Encargado:</span>
                            <span class="font-medium text-right">{{ folio.user?.name || 'Sistema' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-300 pt-1 border-t border-zinc-200/50 dark:border-zinc-700/50">
                            <span class="text-zinc-400">Fechas registradas:</span>
                            <span class="font-mono text-right font-medium text-indigo-600 dark:text-indigo-400">
                                {{ folio.dates_count }} fecha(s)
                            </span>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="grid grid-cols-3 gap-1.5 text-center text-xs">
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <span class="text-[10px] text-zinc-400 uppercase font-semibold block">Nómina</span>
                            <span class="font-mono font-bold text-blue-700 dark:text-blue-400 text-xs">{{ formatCurrency(folio.total_payroll) }}</span>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <span class="text-[10px] text-zinc-400 uppercase font-semibold block">Gastos</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs">{{ formatCurrency(folio.total_expenses) }}</span>
                        </div>
                        <div class="bg-indigo-50 dark:bg-indigo-950/40 p-2 rounded-xl border border-indigo-200 dark:border-indigo-900/50">
                            <span class="text-[10px] text-indigo-700 dark:text-indigo-400 uppercase font-bold block">Total Gasto</span>
                            <span class="font-mono font-bold text-indigo-700 dark:text-indigo-300 text-xs">{{ formatCurrency(folio.total_cost) }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 flex items-center gap-2">
                        <Link :href="`/bitacoras/create?from_folio=${encodeURIComponent(folio.folio_number)}`" class="flex-1">
                            <Button size="sm" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs h-8 rounded-xl font-medium shadow-xs flex items-center justify-center gap-1">
                                <Plus class="h-3.5 w-3.5" />
                                <span>Agregar Actividad</span>
                            </Button>
                        </Link>
                        <Link :href="`/bitacoras/${folio.id}`">
                            <Button size="sm" variant="outline" class="h-8 px-2.5 text-xs rounded-xl" title="Ver detalle del folio">
                                <Eye class="h-3.5 w-3.5" />
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-16 text-center bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 space-y-3">
                <div class="inline-flex p-3 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 rounded-2xl">
                    <FolderKanban class="h-8 w-8" />
                </div>
                <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">No hay folios activos disponibles</h3>
                <p class="text-xs sm:text-sm text-zinc-500 max-w-md mx-auto">
                    Actualmente todos los folios han sido cerrados o no tienes folios creados bajo tus filtros seleccionados.
                </p>
                <div class="pt-2">
                    <Link href="/bitacoras/create">
                        <Button class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold">
                            <Plus class="h-4 w-4 mr-1.5" /> Crear Primera Bitácora
                        </Button>
                    </Link>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 2: CALENDARIO SEMANAL DE GASTOS -->
        <!-- ======================================================== -->
        <div v-if="activeTab === 'expenses'" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <Receipt class="h-5 w-5 text-emerald-600" />
                        Calendario Semanal de Gastos Operativos
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Desglose de compras, viáticos y gastos por cada día de la semana seleccionada.
                    </p>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-800 text-xs">
                    <span class="text-emerald-800 dark:text-emerald-300 font-bold">Total Gastos Semana: {{ formatCurrency(kpis.total_period_expenses) }}</span>
                </div>
            </div>

            <!-- Calendar 7-Day Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-3">
                <div
                    v-for="day in expensesCalendar"
                    :key="day.date"
                    class="bg-white dark:bg-zinc-900 rounded-2xl border shadow-xs flex flex-col overflow-hidden transition"
                    :class="[
                        day.is_today ? 'border-indigo-400 dark:border-indigo-600 ring-2 ring-indigo-300/40 dark:ring-indigo-700/40' : 'border-zinc-200 dark:border-zinc-800',
                        day.is_sunday ? 'bg-amber-50/30 dark:bg-amber-950/10' : ''
                    ]"
                >
                    <!-- Day Header -->
                    <div class="p-3 border-b border-zinc-100 dark:border-zinc-800 text-center" :class="day.is_today ? 'bg-indigo-50/60 dark:bg-indigo-950/40' : 'bg-zinc-50 dark:bg-zinc-800/40'">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                {{ day.day_name }}
                            </span>
                            <Badge v-if="day.is_today" class="bg-indigo-600 text-white text-[9px] py-0 px-1 font-bold">
                                Hoy
                            </Badge>
                            <Badge v-else-if="day.is_sunday" class="bg-amber-500 text-white text-[9px] py-0 px-1 font-bold">
                                Domingo
                            </Badge>
                        </div>
                        <div class="mt-1 flex items-baseline justify-center gap-1 font-mono">
                            <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ day.day_number }}</span>
                            <span class="text-[10px] text-zinc-400">{{ day.date.substring(5, 7) }}</span>
                        </div>
                        <div class="mt-1 pt-1 border-t border-zinc-200/60 dark:border-zinc-700/60">
                            <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                {{ formatCurrency(day.total_amount) }}
                            </span>
                        </div>
                    </div>

                    <!-- Expenses List for Day -->
                    <div class="p-2 space-y-2 flex-1 overflow-y-auto max-h-[360px] divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div
                            v-for="exp in day.expenses"
                            :key="exp.id"
                            class="pt-2 first:pt-0 space-y-1 text-xs"
                        >
                            <div class="flex items-start justify-between gap-1">
                                <span class="font-medium text-zinc-800 dark:text-zinc-200 leading-tight">
                                    {{ exp.concept }}
                                </span>
                                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 shrink-0">
                                    {{ formatCurrency(exp.amount) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-[10px] text-zinc-400">
                                <span class="font-mono font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ exp.folio_number }}
                                </span>
                                <span class="bg-zinc-100 dark:bg-zinc-800 px-1 py-0.5 rounded text-zinc-600 dark:text-zinc-300">
                                    {{ exp.payment_method }}
                                </span>
                            </div>

                            <div v-if="exp.client_name" class="text-[10px] text-zinc-500 truncate">
                                {{ exp.client_name }}
                            </div>
                        </div>

                        <div v-if="day.expenses.length === 0" class="py-8 text-center text-zinc-400 italic text-[11px]">
                            Sin gastos
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 3: CALENDARIO SEMANAL DE PERSONAL A CARGO -->
        <!-- ======================================================== -->
        <div v-if="activeTab === 'personnel'" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <Users class="h-5 w-5 text-purple-600" />
                        Calendario Semanal de Personal a Cargo
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Trabajadores asignados por día, horas laboradas, faltas y actividades ejecutadas.
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap text-xs">
                    <div class="bg-blue-50 dark:bg-blue-950/40 px-3 py-1.5 rounded-xl border border-blue-200 dark:border-blue-800">
                        <span class="text-blue-800 dark:text-blue-300 font-bold">Total Horas: {{ kpis.total_period_hours }} hrs</span>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-950/40 px-3 py-1.5 rounded-xl border border-purple-200 dark:border-purple-800">
                        <span class="text-purple-800 dark:text-purple-300 font-bold">Personal Activo: {{ kpis.total_unique_workers }} trabajadores</span>
                    </div>
                </div>
            </div>

            <!-- Calendar 7-Day Grid for Personnel -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-3">
                <div
                    v-for="day in personnelCalendar"
                    :key="day.date"
                    class="bg-white dark:bg-zinc-900 rounded-2xl border shadow-xs flex flex-col overflow-hidden transition"
                    :class="[
                        day.is_today ? 'border-indigo-400 dark:border-indigo-600 ring-2 ring-indigo-300/40 dark:ring-indigo-700/40' : 'border-zinc-200 dark:border-zinc-800',
                        day.is_sunday ? 'bg-amber-50/30 dark:bg-amber-950/10' : ''
                    ]"
                >
                    <!-- Day Header -->
                    <div class="p-3 border-b border-zinc-100 dark:border-zinc-800 text-center" :class="day.is_today ? 'bg-indigo-50/60 dark:bg-indigo-950/40' : 'bg-zinc-50 dark:bg-zinc-800/40'">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                {{ day.day_name }}
                            </span>
                            <Badge v-if="day.is_today" class="bg-indigo-600 text-white text-[9px] py-0 px-1 font-bold">
                                Hoy
                            </Badge>
                            <Badge v-else-if="day.is_sunday" class="bg-amber-500 text-white text-[9px] py-0 px-1 font-bold">
                                Domingo
                            </Badge>
                        </div>
                        <div class="mt-1 flex items-baseline justify-center gap-1 font-mono">
                            <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ day.day_number }}</span>
                            <span class="text-[10px] text-zinc-400">{{ day.date.substring(5, 7) }}</span>
                        </div>
                        <div class="mt-1 pt-1 border-t border-zinc-200/60 dark:border-zinc-700/60 flex items-center justify-around text-[10px]">
                            <span class="font-bold text-purple-600 dark:text-purple-400">{{ day.workers_count }} trab.</span>
                            <span class="font-mono text-zinc-500">{{ day.total_hours }} hrs</span>
                        </div>
                    </div>

                    <!-- Workers List for Day -->
                    <div class="p-2 space-y-2 flex-1 overflow-y-auto max-h-[360px] divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div
                            v-for="w in day.workers"
                            :key="w.id"
                            class="pt-2 first:pt-0 space-y-1 text-xs"
                        >
                            <!-- Worker Name & Status -->
                            <div class="flex items-start justify-between gap-1">
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 leading-tight">
                                    {{ w.employee_name }}
                                </span>
                                <Badge v-if="w.is_absent" class="bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 text-[9px] py-0 px-1">
                                    Falta
                                </Badge>
                                <Badge v-else-if="w.is_partial_shift" class="bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 text-[9px] py-0 px-1">
                                    T. Parcial
                                </Badge>
                                <span v-else class="font-mono font-semibold text-blue-600 dark:text-blue-400 shrink-0 text-[11px]">
                                    {{ w.total_hours }} hrs
                                </span>
                            </div>

                            <!-- Folio & Hours breakdown -->
                            <div class="flex items-center justify-between text-[10px] text-zinc-500">
                                <span class="font-mono font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ w.folio_number }}
                                </span>
                                <span v-if="!w.is_absent">
                                    {{ w.hours_worked }}h ord. <span v-if="w.overtime_hours > 0" class="text-amber-600">+{{ w.overtime_hours }}h ext</span>
                                </span>
                            </div>

                            <!-- Activity Description -->
                            <div v-if="w.activity_description" class="text-[10px] text-zinc-500 dark:text-zinc-400 italic line-clamp-2">
                                "{{ w.activity_description }}"
                            </div>
                        </div>

                        <div v-if="day.workers.length === 0" class="py-8 text-center text-zinc-400 italic text-[11px]">
                            Sin personal asignado
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
