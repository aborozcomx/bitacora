<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Calculator,
    Calendar,
    Building,
    Clock,
    DollarSign,
    Users,
    TrendingUp,
    FileSpreadsheet,
    ChevronLeft,
    ChevronRight,
    ChevronDown,
    ChevronUp,
    FileText,
    ExternalLink
} from '@lucide/vue';

interface Branch {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface BitacoraEntry {
    id: number;
    folio_number: string;
    date: string;
    is_sunday: boolean;
    branch_name: string;
    user_name?: string;
    hours_worked: number;
    overtime_hours: number;
    total_earned: number;
    is_absent: boolean;
    is_partial_shift?: boolean;
    partial_shift_reason?: string | null;
}

interface EmployeeFolioBreakdown {
    folio_number: string;
    dates: string[];
    days_count: number;
    total_hours_worked: number;
    total_overtime_hours: number;
    total_earned: number;
    has_sunday: boolean;
    daily_records: BitacoraEntry[];
}

interface PeriodFolioDateBreakdown {
    date: string;
    is_sunday: boolean;
    bitacora_id: number;
    employees_count: number;
    regular_hours: number;
    overtime_hours: number;
    regular_pay: number;
    overtime_pay: number;
    total_pay: number;
    absences_count: number;
}

interface PeriodGroupedFolio {
    folio_number: string;
    branch_name: string;
    days_count: number;
    dates: PeriodFolioDateBreakdown[];
    employees_count: number;
    total_regular_hours: number;
    total_overtime_hours: number;
    regular_pay: number;
    overtime_pay: number;
    total_pay: number;
}

interface PayrollItem {
    employee_id: number;
    employee_code: string;
    full_name: string;
    branch_name: string;
    base_hourly_rate: number;
    overtime_hourly_rate: number;
    total_regular_hours: number;
    total_overtime_hours: number;
    regular_pay: number;
    overtime_pay: number;
    total_pay: number;
    bitacora_count: number;
    unique_folios_count?: number;
    has_sunday?: boolean;
    absences_count: number;
    absences_dates: string[];
    partial_shifts_count?: number;
    bitacoras: BitacoraEntry[];
    by_folio?: EmployeeFolioBreakdown[];
}

interface Totals {
    grand_regular_hours: number;
    grand_overtime_hours: number;
    grand_regular_pay: number;
    grand_overtime_pay: number;
    grand_total_pay: number;
    grand_absences_count: number;
    grand_employees_with_absences: number;
    grand_partial_shifts_count?: number;
}

const props = defineProps<{
    payrollSummary: PayrollItem[];
    byFolio?: PeriodGroupedFolio[];
    branches: Branch[];
    users: User[];
    filters: {
        start_date: string;
        end_date: string;
        branch_id: string | null;
        user_id?: string | null;
        absence_filter?: string;
    };
    totals: Totals;
}>();

const activeTab = ref<'employees' | 'folios'>('employees');

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const branchId = ref(props.filters.branch_id || '');
const userId = ref(props.filters.user_id || '');
const absenceFilter = ref(props.filters.absence_filter || 'all');
const expandedEmployeeIds = ref<number[]>([]);

const toggleEmployeeExpand = (empId: number) => {
    if (expandedEmployeeIds.value.includes(empId)) {
        expandedEmployeeIds.value = expandedEmployeeIds.value.filter(id => id !== empId);
    } else {
        expandedEmployeeIds.value.push(empId);
    }
};

const handleFilter = () => {
    router.get('/salaries', {
        start_date: startDate.value,
        end_date: endDate.value,
        branch_id: branchId.value,
        user_id: userId.value,
        absence_filter: absenceFilter.value,
    }, { preserveState: true, replace: true });
};

// Format Date to YYYY-MM-DD using local time
const formatLocalDate = (d: Date): string => {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Weekly period calculation (Thursday to following Wednesday - 7 days)
const getWeeklyPeriods = () => {
    const periods = [];
    const today = new Date();
    const currentDay = today.getDay(); // 0: Sun, 1: Mon, 2: Tue, 3: Wed, 4: Thu, 5: Fri, 6: Sat
    const diffToThu = (currentDay < 4 ? currentDay + 7 : currentDay) - 4;
    
    const baseThu = new Date(today.getFullYear(), today.getMonth(), today.getDate() - diffToThu);

    for (let i = 2; i >= -8; i--) {
        const thu = new Date(baseThu.getFullYear(), baseThu.getMonth(), baseThu.getDate() + (i * 7));
        const wed = new Date(thu.getFullYear(), thu.getMonth(), thu.getDate() + 6);

        const startStr = formatLocalDate(thu);
        const endStr = formatLocalDate(wed);
        
        let label = `Semana: Jue ${startStr} al Mié ${endStr}`;
        if (i === 0) label += ' (Semana Actual)';

        periods.push({
            start: startStr,
            end: endStr,
            label,
        });
    }
    return periods.reverse();
};

const weeklyPeriods = computed(() => getWeeklyPeriods());

const selectedWeeklyPeriod = computed({
    get: () => `${startDate.value}|${endDate.value}`,
    set: (val: string) => {
        if (!val) return;
        const [s, e] = val.split('|');
        if (s && e) {
            startDate.value = s;
            endDate.value = e;
            handleFilter();
        }
    }
});

const applyPreviousWeek = () => {
    const currentStart = new Date(startDate.value + 'T00:00:00');
    currentStart.setDate(currentStart.getDate() - 7);
    const currentEnd = new Date(endDate.value + 'T00:00:00');
    currentEnd.setDate(currentEnd.getDate() - 7);

    startDate.value = formatLocalDate(currentStart);
    endDate.value = formatLocalDate(currentEnd);
    handleFilter();
};

const applyCurrentWeek = () => {
    const today = new Date();
    const currentDay = today.getDay();
    const diffToThu = (currentDay < 4 ? currentDay + 7 : currentDay) - 4;
    const thu = new Date(today.getFullYear(), today.getMonth(), today.getDate() - diffToThu);
    const wed = new Date(thu.getFullYear(), thu.getMonth(), thu.getDate() + 6);

    startDate.value = formatLocalDate(thu);
    endDate.value = formatLocalDate(wed);
    handleFilter();
};

const applyNextWeek = () => {
    const currentStart = new Date(startDate.value + 'T00:00:00');
    currentStart.setDate(currentStart.getDate() + 7);
    const currentEnd = new Date(endDate.value + 'T00:00:00');
    currentEnd.setDate(currentEnd.getDate() + 7);

    startDate.value = formatLocalDate(currentStart);
    endDate.value = formatLocalDate(currentEnd);
    handleFilter();
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="Cálculo de Salarios" />

    <div class="p-3 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full min-w-0 print:p-0 print:max-w-none">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-4 sm:p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm print:hidden">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <Calculator class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Cálculo Automático de Salarios y Desglose de Bitácoras
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Cálculo semanal de salarios (ciclo <strong>Jueves a Miércoles</strong>) y desglose de bitácoras por empleado.
                </p>
            </div>
            <Button variant="outline" @click="printReport" class="rounded-xl w-full sm:w-auto">
                <FileSpreadsheet class="h-4 w-4 mr-2" /> Imprimir Reporte
            </Button>
        </div>

        <!-- Weekly Filter Quick Controls -->
        <div class="bg-white dark:bg-zinc-900 p-3 sm:p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3 print:hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold uppercase text-indigo-600 dark:text-indigo-400 tracking-wider flex items-center gap-1.5">
                        <Calendar class="h-4 w-4" /> Período Semanal (Jueves a Miércoles):
                    </span>
                    <span class="text-xs font-mono font-semibold text-zinc-800 dark:text-zinc-200 bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-1 rounded-md border border-indigo-200 dark:border-indigo-800">
                        {{ startDate }} al {{ endDate }}
                    </span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <Button variant="outline" size="sm" @click="applyPreviousWeek" class="rounded-lg text-xs flex-1 sm:flex-initial">
                        <ChevronLeft class="h-3.5 w-3.5 mr-1" /> Semana Anterior
                    </Button>
                    <Button variant="secondary" size="sm" @click="applyCurrentWeek" class="rounded-lg text-xs font-semibold flex-1 sm:flex-initial">
                        Semana Actual (Jue - Mié)
                    </Button>
                    <Button variant="outline" size="sm" @click="applyNextWeek" class="rounded-lg text-xs flex-1 sm:flex-initial">
                        Semana Siguiente <ChevronRight class="h-3.5 w-3.5 ml-1" />
                    </Button>
                </div>
            </div>

            <!-- Filters Form Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-1">
                <div>
                    <label class="text-xs font-semibold text-zinc-500 block mb-1">Selector Rápido de Semana</label>
                    <select
                        v-model="selectedWeeklyPeriod"
                        class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-3 text-xs"
                    >
                        <option
                            v-for="p in weeklyPeriods"
                            :key="`${p.start}|${p.end}`"
                            :value="`${p.start}|${p.end}`"
                        >
                            {{ p.label }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-zinc-500 block mb-1">Rango Personalizado</label>
                    <div class="flex items-center gap-1.5">
                        <Input type="date" v-model="startDate" @change="handleFilter" class="bg-zinc-50 dark:bg-zinc-800/50 text-xs h-9" />
                        <span class="text-zinc-400 text-xs">-</span>
                        <Input type="date" v-model="endDate" @change="handleFilter" class="bg-zinc-50 dark:bg-zinc-800/50 text-xs h-9" />
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-zinc-500 block mb-1">Sucursal</label>
                    <select
                        v-model="branchId"
                        @change="handleFilter"
                        class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-3 text-xs"
                    >
                        <option value="">Todas las Sucursales</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-zinc-500 block mb-1">Encargado Responsable</label>
                    <select
                        v-model="userId"
                        @change="handleFilter"
                        class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-3 text-xs"
                    >
                        <option value="">Todos los Encargados</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-zinc-500 block mb-1">Filtro de Asistencia y Turnos</label>
                    <select
                        v-model="absenceFilter"
                        @change="handleFilter"
                        class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-3 text-xs"
                    >
                        <option value="all">Todos los empleados</option>
                        <option value="with_absences">Solo con faltas registradas</option>
                        <option value="with_partial_shifts">Solo con jornadas parciales (Solo N hrs)</option>
                        <option value="without_absences">Solo sin faltas (asistencia completa)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <Card class="rounded-2xl border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-medium uppercase text-zinc-500">Horas Base Totales</CardTitle>
                    <Clock class="h-4 w-4 text-emerald-600" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ totals.grand_regular_hours }} hrs
                    </div>
                    <p class="text-xs text-emerald-600 mt-1 font-semibold">
                        Subtotal: ${{ totals.grand_regular_pay.toFixed(2) }}
                    </p>
                </CardContent>
            </Card>

            <Card class="rounded-2xl border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-medium uppercase text-zinc-500">Horas Extras Totales</CardTitle>
                    <TrendingUp class="h-4 w-4 text-amber-600" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ totals.grand_overtime_hours }} hrs
                    </div>
                    <p class="text-xs text-amber-600 mt-1 font-semibold">
                        Subtotal Extras: ${{ totals.grand_overtime_pay.toFixed(2) }}
                    </p>
                </CardContent>
            </Card>

            <Card class="rounded-2xl border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-medium uppercase text-zinc-500">Total Empleados</CardTitle>
                    <Users class="h-4 w-4 text-indigo-600" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ payrollSummary.length }} personal
                    </div>
                    <p class="text-xs text-zinc-400 mt-1">En el reporte</p>
                </CardContent>
            </Card>

            <Card class="rounded-2xl border-red-200 dark:border-red-950/60 shadow-sm bg-red-50/40 dark:bg-red-950/20">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-medium uppercase text-red-700 dark:text-red-300">Faltas Registradas</CardTitle>
                    <Badge variant="destructive" class="text-[10px] px-1.5 py-0">{{ totals.grand_absences_count }}</Badge>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400 font-mono">
                        {{ totals.grand_absences_count }} faltas
                    </div>
                    <p class="text-xs text-red-600/80 mt-1 font-medium">
                        {{ totals.grand_employees_with_absences }} personal con falta
                        <span v-if="(totals.grand_partial_shifts_count || 0) > 0" class="text-blue-600 dark:text-blue-400 block mt-0.5">
                            ⏱️ {{ totals.grand_partial_shifts_count }} jornada(s) parcial(es)
                        </span>
                    </p>
                </CardContent>
            </Card>

            <Card class="rounded-2xl border-indigo-200 dark:border-indigo-900/60 shadow-sm bg-indigo-50/50 dark:bg-indigo-950/30">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-medium uppercase text-indigo-700 dark:text-indigo-300">Total a Pagar</CardTitle>
                    <DollarSign class="h-4 w-4 text-indigo-600" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 font-mono">
                        ${{ totals.grand_total_pay.toFixed(2) }}
                    </div>
                    <p class="text-xs text-indigo-600/80 mt-1 font-medium">Nómina total líquida</p>
                </CardContent>
            </Card>
        </div>

        <!-- TABS SWITCHER -->
        <div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-2 print:hidden">
            <Button
                :variant="activeTab === 'employees' ? 'default' : 'outline'"
                size="sm"
                @click="activeTab = 'employees'"
                class="rounded-xl text-xs gap-1.5 shadow-sm"
                :class="activeTab === 'employees' ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : ''"
            >
                <Users class="h-3.5 w-3.5" />
                Desglose por Empleado ({{ payrollSummary.length }})
            </Button>
            <Button
                :variant="activeTab === 'folios' ? 'default' : 'outline'"
                size="sm"
                @click="activeTab = 'folios'"
                class="rounded-xl text-xs gap-1.5 shadow-sm"
                :class="activeTab === 'folios' ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : ''"
            >
                <FileSpreadsheet class="h-3.5 w-3.5" />
                Sumatoria Consolidada por Folio ({{ byFolio?.length || 0 }} folios)
            </Button>
        </div>

        <!-- PAYROLL SUMMARY TABLE (EMPLOYEES) -->
        <div v-show="activeTab === 'employees'" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm w-full min-w-0">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                <h2 class="font-bold text-zinc-900 dark:text-zinc-100 text-base">
                    Desglose Semanal de Nómina y Bitácoras ({{ startDate }} al {{ endDate }})
                </h2>
                <span class="text-xs text-zinc-500">
                    Haz clic en <ChevronDown class="inline h-3.5 w-3.5 text-zinc-400" /> para ver las bitácoras detalladas por empleado.
                </span>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-xs sm:text-sm text-zinc-600 dark:text-zinc-300 min-w-[850px]">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-semibold text-zinc-500 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center"></th>
                            <th class="py-3 px-3">Código</th>
                            <th class="py-3 px-4">Empleado</th>
                            <th class="py-3 px-4">Sucursal</th>
                            <th class="py-3 px-4">Bitácoras Participadas</th>
                            <th class="py-3 px-4 text-center">Faltas</th>
                            <th class="py-3 px-4 text-center">Hrs Norm.</th>
                            <th class="py-3 px-4 text-center">Hrs Ext.</th>
                            <th class="py-3 px-4 text-right">Pago Normal</th>
                            <th class="py-3 px-4 text-right">Pago Extra</th>
                            <th class="py-3 px-4 text-right">Salario Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <template v-for="item in payrollSummary" :key="item.employee_id">
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-3 px-3 text-center">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200"
                                        @click="toggleEmployeeExpand(item.employee_id)"
                                        :title="expandedEmployeeIds.includes(item.employee_id) ? 'Ocultar bitácoras' : 'Ver bitácoras detalladas'"
                                    >
                                        <ChevronUp v-if="expandedEmployeeIds.includes(item.employee_id)" class="h-4 w-4" />
                                        <ChevronDown v-else class="h-4 w-4" />
                                    </Button>
                                </td>
                                <td class="py-3.5 px-3 font-mono font-medium text-indigo-600 dark:text-indigo-400">
                                    {{ item.employee_code }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ item.full_name }}
                                </td>
                                <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400">
                                    {{ item.branch_name }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap items-center gap-1.5 max-w-xs">
                                        <Link
                                            v-for="b in item.bitacoras"
                                            :key="b.id"
                                            :href="`/bitacoras/${b.id}`"
                                            class="inline-flex items-center gap-1 text-[11px] font-mono px-2 py-0.5 rounded-md border transition hover:underline"
                                            :class="b.is_absent
                                                ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-900'
                                                : (b.is_sunday
                                                    ? 'bg-amber-100 text-amber-900 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700 font-bold'
                                                    : (b.is_partial_shift
                                                        ? 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900 font-semibold'
                                                        : 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-900'))"
                                            :title="`${b.folio_number} (${b.date}) ${b.is_sunday ? '[Domingo]' : ''} ${b.is_partial_shift ? '[Jornada Parcial: ' + b.hours_worked + 'h]' : ''} - ${b.is_absent ? 'Falta' : b.hours_worked + 'h + ' + b.overtime_hours + 'h extra'}`"
                                        >
                                            <span v-if="b.is_absent">⚠️</span>
                                            <span v-else-if="b.is_sunday">☀️</span>
                                            <span v-else-if="b.is_partial_shift">⏱️</span>
                                            <span>{{ b.folio_number }}</span>
                                            <span class="text-zinc-400">({{ b.date }})</span>
                                            <span v-if="b.is_partial_shift" class="text-[9px] bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 px-1 rounded font-bold">
                                                {{ b.hours_worked }}h
                                            </span>
                                        </Link>
                                        <span v-if="!item.bitacoras || item.bitacoras.length === 0" class="text-xs text-zinc-400 italic">
                                            Sin registros
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <div v-if="item.absences_count > 0" class="flex flex-col items-center gap-0.5">
                                            <Badge variant="destructive" class="bg-red-500 hover:bg-red-600 text-white font-mono text-xs">
                                                {{ item.absences_count }} {{ item.absences_count === 1 ? 'falta' : 'faltas' }}
                                            </Badge>
                                            <span v-if="item.absences_dates?.length > 0" class="text-[10px] text-zinc-500 font-mono">
                                                {{ item.absences_dates.join(', ') }}
                                            </span>
                                        </div>
                                        <Badge v-if="(item.partial_shifts_count || 0) > 0" class="bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 text-[10px] py-0 font-medium">
                                            ⏱️ {{ item.partial_shifts_count }} parcial{{ item.partial_shifts_count === 1 ? '' : 'es' }}
                                        </Badge>
                                        <span v-if="item.absences_count === 0 && (!item.partial_shifts_count || item.partial_shifts_count === 0)" class="text-zinc-400 text-xs">
                                            0
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center font-medium">
                                    {{ item.total_regular_hours }} hrs
                                </td>
                                <td class="py-3.5 px-4 text-center font-semibold text-amber-600 dark:text-amber-400">
                                    {{ item.total_overtime_hours }} hrs
                                </td>
                                <td class="py-3.5 px-4 text-right font-medium text-emerald-600 dark:text-emerald-400">
                                    ${{ item.regular_pay.toFixed(2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-medium text-amber-600 dark:text-amber-400">
                                    ${{ item.overtime_pay.toFixed(2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-zinc-900 dark:text-zinc-100">
                                    ${{ item.total_pay.toFixed(2) }}
                                </td>
                            </tr>

                            <!-- Expanded row with bitacora breakdown -->
                            <tr v-if="expandedEmployeeIds.includes(item.employee_id)" class="bg-zinc-50/80 dark:bg-zinc-800/40 border-b border-zinc-200 dark:border-zinc-800">
                                <td colspan="11" class="p-4 pl-12">
                                    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                                <FileText class="h-4 w-4 text-indigo-600" />
                                                Bitácoras del Empleado: {{ item.full_name }} ({{ item.employee_code }})
                                            </h4>
                                            <span class="text-xs text-zinc-500 font-medium">
                                                {{ item.unique_folios_count || item.bitacoras.length }} {{ (item.unique_folios_count || item.bitacoras.length) === 1 ? 'folio único' : 'folios únicos' }} ({{ item.bitacoras.length }} {{ item.bitacoras.length === 1 ? 'registro' : 'registros diarios' }})
                                            </span>
                                        </div>

                                        <!-- Sumatoria Acumulada por Folio para el empleado -->
                                        <div v-if="item.by_folio && item.by_folio.length > 0" class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/80 dark:border-zinc-700/80">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5">
                                                    <Calculator class="h-3.5 w-3.5" />
                                                    Sumatoria Acumulada por Folio de este Empleado
                                                </span>
                                                <span class="text-[11px] text-zinc-500">
                                                    Horas y salarios sumados por folio
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                                <div
                                                    v-for="f in item.by_folio"
                                                    :key="f.folio_number"
                                                    class="p-2.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-xs space-y-1.5 shadow-sm"
                                                >
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm">
                                                            {{ f.folio_number }}
                                                        </span>
                                                        <Badge v-if="f.days_count > 1" class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 text-[10px] py-0 font-semibold">
                                                            🗓️ {{ f.days_count }} días
                                                        </Badge>
                                                        <Badge v-else variant="outline" class="text-[10px] py-0 text-zinc-500">
                                                            1 día
                                                        </Badge>
                                                    </div>
                                                    <div class="text-[11px] text-zinc-600 dark:text-zinc-400">
                                                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">Fechas trabajadas:</span>
                                                        <div class="flex flex-wrap gap-1 mt-0.5 font-mono">
                                                            <span
                                                                v-for="d in f.dates"
                                                                :key="d"
                                                                class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-[10px]"
                                                            >
                                                                {{ d }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="pt-1 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between font-mono">
                                                        <span class="text-zinc-500">
                                                            {{ f.total_hours_worked }}h norm. <span v-if="f.total_overtime_hours > 0" class="text-amber-600">+{{ f.total_overtime_hours }}h ext.</span>
                                                        </span>
                                                        <span class="font-bold text-emerald-600 text-xs">
                                                            ${{ f.total_earned.toFixed(2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <table class="w-full text-xs text-left">
                                            <thead class="bg-zinc-100 dark:bg-zinc-800 text-zinc-500 font-semibold uppercase">
                                                <tr>
                                                    <th class="py-2 px-3">Folio Bitácora</th>
                                                    <th class="py-2 px-3">Fecha</th>
                                                    <th class="py-2 px-3">Sucursal</th>
                                                    <th class="py-2 px-3">Encargado</th>
                                                    <th class="py-2 px-3 text-center">Estado</th>
                                                    <th class="py-2 px-3 text-center">Hrs Normales</th>
                                                    <th class="py-2 px-3 text-center">Hrs Extras</th>
                                                    <th class="py-2 px-3 text-right">Ganado</th>
                                                    <th class="py-2 px-3 text-right">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                                <tr
                                                    v-for="b in item.bitacoras"
                                                    :key="b.id"
                                                    :class="b.is_sunday ? 'bg-amber-50/70 dark:bg-amber-950/30' : ''"
                                                >
                                                    <td class="py-2 px-3 font-mono font-medium text-indigo-600 dark:text-indigo-400">
                                                        {{ b.folio_number }}
                                                    </td>
                                                    <td class="py-2 px-3 font-mono text-zinc-600 dark:text-zinc-400">
                                                        <span class="flex items-center gap-1">
                                                            {{ b.date }}
                                                            <Badge v-if="b.is_sunday" class="bg-amber-500 text-white text-[9px] py-0 px-1 font-semibold">☀️ Domingo</Badge>
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-3">{{ b.branch_name || '-' }}</td>
                                                    <td class="py-2 px-3 text-zinc-600 dark:text-zinc-400">{{ b.user_name || '-' }}</td>
                                                    <td class="py-2 px-3 text-center">
                                                        <Badge v-if="b.is_absent" variant="destructive" class="text-[10px] py-0">Falta</Badge>
                                                        <div v-else-if="b.is_partial_shift" class="flex flex-col items-center gap-0.5">
                                                            <Badge class="bg-blue-100 text-blue-800 dark:bg-blue-950 text-blue-300 border-blue-300 text-[10px] py-0 font-semibold">
                                                                ⏱️ Parcial ({{ b.hours_worked }}h)
                                                            </Badge>
                                                            <span v-if="b.partial_shift_reason" class="text-[9px] text-blue-600 dark:text-blue-400 italic">
                                                                {{ b.partial_shift_reason }}
                                                            </span>
                                                        </div>
                                                        <Badge v-else variant="outline" class="text-[10px] py-0 text-emerald-600 border-emerald-300">Asistió</Badge>
                                                    </td>
                                                    <td class="py-2 px-3 text-center">{{ b.hours_worked }} hrs</td>
                                                    <td class="py-2 px-3 text-center text-amber-600 font-medium">{{ b.overtime_hours }} hrs</td>
                                                    <td class="py-2 px-3 text-right font-mono font-bold text-emerald-600">${{ b.total_earned.toFixed(2) }}</td>
                                                    <td class="py-2 px-3 text-right">
                                                        <Link :href="`/bitacoras/${b.id}`" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 inline-flex items-center gap-1 font-semibold">
                                                            Ver <ExternalLink class="h-3 w-3" />
                                                        </Link>
                                                    </td>
                                                </tr>
                                                <tr v-if="item.bitacoras.length === 0">
                                                    <td colspan="9" class="py-4 text-center text-zinc-400 italic">
                                                        No hay bitácoras asociadas en el rango de fechas seleccionado.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-if="payrollSummary.length === 0">
                            <td colspan="11" class="py-10 text-center text-zinc-400">
                                No hay registros para los empleados en el rango de fechas y filtros seleccionados.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="payrollSummary.length > 0" class="bg-zinc-100 dark:bg-zinc-800/80 font-semibold text-zinc-900 dark:text-zinc-100">
                        <tr>
                            <td colspan="5" class="py-3.5 px-4 uppercase text-xs">Totales del Período</td>
                            <td class="py-3.5 px-4 text-center font-mono text-red-600">{{ totals.grand_absences_count }} faltas</td>
                            <td class="py-3.5 px-4 text-center font-mono">{{ totals.grand_regular_hours }} hrs</td>
                            <td class="py-3.5 px-4 text-center font-mono text-amber-600">{{ totals.grand_overtime_hours }} hrs</td>
                            <td class="py-3.5 px-4 text-right font-mono text-emerald-600">${{ totals.grand_regular_pay.toFixed(2) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-amber-600">${{ totals.grand_overtime_pay.toFixed(2) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-lg text-indigo-600 dark:text-indigo-400">${{ totals.grand_total_pay.toFixed(2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- FOLIOS CONSOLIDATED VIEW -->
        <div v-if="activeTab === 'folios'" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm w-full min-w-0">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                <div>
                    <h2 class="font-bold text-zinc-900 dark:text-zinc-100 text-base flex items-center gap-2">
                        <FileSpreadsheet class="h-5 w-5 text-indigo-600" />
                        Sumatoria Consolidada de Nómina por Folio ({{ startDate }} al {{ endDate }})
                    </h2>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        Acumulado general de horas y salarios por folio. Si el folio se utilizó en diferentes días, se muestra el desglose por fecha y el acumulado final.
                    </p>
                </div>
                <span class="text-xs text-zinc-500">
                    Nómina Total: <strong class="font-mono text-indigo-600 text-sm">${{ totals.grand_total_pay.toFixed(2) }}</strong>
                </span>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-xs sm:text-sm text-zinc-600 dark:text-zinc-300 min-w-[850px]">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-semibold text-zinc-500 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-4">Folio Bitácora</th>
                            <th class="py-3 px-4">Sucursal</th>
                            <th class="py-3 px-4">Días y Fechas Trabajadas (Desglose Diario)</th>
                            <th class="py-3 px-4 text-center">Personal</th>
                            <th class="py-3 px-4 text-center">Hrs Normales</th>
                            <th class="py-3 px-4 text-center">Hrs Extras</th>
                            <th class="py-3 px-4 text-right">Pago Normal</th>
                            <th class="py-3 px-4 text-right">Pago Extra</th>
                            <th class="py-3 px-4 text-right">Total Acumulado ($)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <tr v-for="item in byFolio" :key="item.folio_number" class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40">
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                <span class="text-sm">{{ item.folio_number }}</span>
                                <div v-if="item.days_count > 1" class="mt-1">
                                    <Badge class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 text-[10px] py-0 font-semibold">
                                        🗓️ Multi-fecha ({{ item.days_count }} días)
                                    </Badge>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-zinc-900 dark:text-zinc-100">
                                {{ item.branch_name }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col gap-1.5">
                                    <div
                                        v-for="d in item.dates"
                                        :key="d.date"
                                        class="inline-flex items-center gap-2 text-xs font-mono"
                                    >
                                        <Link
                                            :href="`/bitacoras/${d.bitacora_id}`"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800/80 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition text-zinc-800 dark:text-zinc-200"
                                        >
                                            <Calendar class="h-3 w-3 text-zinc-400" />
                                            <strong>{{ d.date }}</strong>
                                            <Badge v-if="d.is_sunday" class="bg-amber-500 text-white text-[9px] py-0 px-1 font-semibold">☀️ Dom</Badge>
                                            <span class="text-zinc-400">•</span>
                                            <span>{{ d.employees_count }} emp.</span>
                                            <span class="text-zinc-400">•</span>
                                            <span>{{ d.regular_hours }}h norm. <span v-if="d.overtime_hours > 0" class="text-amber-600">+{{ d.overtime_hours }}h ext.</span></span>
                                            <span class="text-zinc-400">•</span>
                                            <span class="text-emerald-600 font-semibold">${{ d.total_pay.toFixed(2) }}</span>
                                            <ExternalLink class="h-3 w-3 ml-0.5 text-zinc-400" />
                                        </Link>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-medium">
                                {{ item.employees_count }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                {{ item.total_regular_hours }} hrs
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-amber-600">
                                {{ item.total_overtime_hours }} hrs
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-emerald-600">
                                ${{ item.regular_pay.toFixed(2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-amber-600">
                                ${{ item.overtime_pay.toFixed(2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-indigo-600 dark:text-indigo-400 font-mono text-base">
                                ${{ item.total_pay.toFixed(2) }}
                            </td>
                        </tr>
                        <tr v-if="!byFolio || byFolio.length === 0">
                            <td colspan="9" class="py-10 text-center text-zinc-400">
                                No hay folios con personal registrado en el período seleccionado.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="byFolio && byFolio.length > 0" class="bg-zinc-100 dark:bg-zinc-800/80 font-semibold text-zinc-900 dark:text-zinc-100">
                        <tr>
                            <td colspan="4" class="py-3.5 px-4 uppercase text-xs">Total Consolidado de Todos los Folios</td>
                            <td class="py-3.5 px-4 text-center font-mono">{{ totals.grand_regular_hours }} hrs</td>
                            <td class="py-3.5 px-4 text-center font-mono text-amber-600">{{ totals.grand_overtime_hours }} hrs</td>
                            <td class="py-3.5 px-4 text-right font-mono text-emerald-600">${{ totals.grand_regular_pay.toFixed(2) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-amber-600">${{ totals.grand_overtime_pay.toFixed(2) }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-lg text-indigo-600 dark:text-indigo-400">${{ totals.grand_total_pay.toFixed(2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</template>
