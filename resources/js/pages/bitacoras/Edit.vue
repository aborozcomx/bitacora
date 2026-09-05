<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
    Plus,
    Trash2,
    Save,
    ArrowLeft,
    ClipboardList,
    Users,
    Receipt,
    Calendar,
    AlertCircle,
    Building2,
    UserCheck,
    ListCheck,
    Sun,
    DollarSign,
    Clock,
    CheckCircle2,
    ShieldAlert,
    FileText
} from '@lucide/vue';

interface Branch { id: number; name: string; }
interface User { id: number; name: string; email: string; }
interface ClientBranch { id: number; name: string; code: string | null; }
interface Client { id: number; name: string; code: string; branches: ClientBranch[]; }
interface ActivityType { id: number; name: string; }
interface Employee {
    id: number;
    first_name: string;
    last_name: string;
    employee_code: string;
    branch_id: number;
    base_hourly_rate: number | string;
    overtime_hourly_rate: number | string;
}
interface PaymentMethod { id: number; name: string; slug: string; requires_card_details: boolean; }
interface PaymentCard {
    id: number;
    alias: string;
    payment_method_id: number;
    bank_name: string | null;
    card_number_masked: string | null;
}

interface ActivityEmployeeForm {
    id?: number;
    employee_id: number | '';
    is_absent: boolean;
    hours_worked: number;
    overtime_hours: number;
}

interface ActivityExpenseForm {
    id?: number;
    concept: string;
    amount: number | '';
    payment_method_id: number | '';
    payment_card_id: number | '';
    reference_number: string;
    notes: string;
}

interface ActivityForm {
    id?: number;
    activity_type_id: number | '';
    date: string;
    description: string;
    employees: ActivityEmployeeForm[];
    expenses: ActivityExpenseForm[];
}

const props = defineProps<{
    bitacora: {
        id: number;
        branch_id: number;
        user_id: number;
        client_id: number;
        client_branch_id: number | null;
        folio_number: string;
        folio_prefix: string | null;
        folio_consecutive: string | null;
        date: string;
        notes: string | null;
        branch?: { name: string };
        user?: { name: string; email: string };
        client?: { name: string; code: string };
        client_branch?: { name: string; code: string | null };
        clientBranch?: { name: string; code: string | null };
        activities?: any[];
    };
    branches: Branch[];
    users: User[];
    clients: Client[];
    activityTypes: ActivityType[];
    employees: Employee[];
    paymentMethods: PaymentMethod[];
    paymentCards: PaymentCard[];
    isAdmin: boolean;
}>();

const today = new Date().toISOString().substring(0, 10);

// Initialize activities structure from props
const initialActivities: ActivityForm[] = (props.bitacora.activities && props.bitacora.activities.length > 0)
    ? props.bitacora.activities.map(act => ({
        id: act.id,
        activity_type_id: act.activity_type_id || '',
        date: act.date || props.bitacora.date || today,
        description: act.description || '',
        employees: (act.employees || []).map((emp: any) => ({
            id: emp.id,
            employee_id: emp.employee_id,
            is_absent: Boolean(emp.is_absent),
            hours_worked: Number(emp.hours_worked) || 0,
            overtime_hours: Number(emp.overtime_hours) || 0,
        })),
        expenses: (act.expenses || []).map((exp: any) => ({
            id: exp.id,
            concept: exp.concept || '',
            amount: Number(exp.amount) || '',
            payment_method_id: exp.payment_method_id || '',
            payment_card_id: exp.payment_card_id || '',
            reference_number: exp.reference_number || '',
            notes: exp.notes || '',
        })),
    }))
    : [{
        activity_type_id: '',
        date: props.bitacora.date || today,
        description: '',
        employees: [],
        expenses: [],
    }];

const form = useForm({
    branch_id: props.bitacora.branch_id,
    user_id: props.bitacora.user_id,
    client_id: props.bitacora.client_id,
    client_branch_id: props.bitacora.client_branch_id || '',
    folio_prefix: props.bitacora.folio_prefix || '',
    folio_consecutive: props.bitacora.folio_consecutive || '',
    folio_number: props.bitacora.folio_number || '',
    date: props.bitacora.date || today,
    notes: props.bitacora.notes || '',
    activities: initialActivities,
});

// Helper for date evaluation
const isSundayDate = (dateStr: string): boolean => {
    if (!dateStr) return false;
    const d = new Date(dateStr + 'T00:00:00');
    return d.getDay() === 0;
};

const isSaturdayDate = (dateStr: string): boolean => {
    if (!dateStr) return false;
    const d = new Date(dateStr + 'T00:00:00');
    return d.getDay() === 6;
};

const getMaxHoursForDate = (dateStr: string): number => {
    return isSaturdayDate(dateStr) ? 6 : 8;
};

// Activity methods
const addActivity = () => {
    form.activities.push({
        activity_type_id: '',
        date: form.date || today,
        description: '',
        employees: [],
        expenses: [],
    });
};

const removeActivity = (index: number) => {
    if (form.activities.length > 1) {
        form.activities.splice(index, 1);
    }
};

// Nested Employees methods
const addEmployeeToActivity = (actIndex: number) => {
    const act = form.activities[actIndex];
    // Find first employee not already added to this activity
    const existingEmpIds = act.employees.map(e => Number(e.employee_id));
    const available = props.employees.find(e => !existingEmpIds.includes(e.id));
    const defaultEmpId = available ? available.id : (props.employees[0]?.id || '');

    act.employees.push({
        employee_id: defaultEmpId,
        is_absent: false,
        hours_worked: 8,
        overtime_hours: 0,
    });
};

const removeEmployeeFromActivity = (actIndex: number, empIndex: number) => {
    form.activities[actIndex].employees.splice(empIndex, 1);
};

const toggleAbsent = (empRow: ActivityEmployeeForm) => {
    empRow.is_absent = !empRow.is_absent;
    if (empRow.is_absent) {
        empRow.hours_worked = 0;
        empRow.overtime_hours = 0;
    } else {
        empRow.hours_worked = 8;
    }
};

const getEmployeeRate = (empId: number | string, type: 'base' | 'overtime') => {
    if (!empId) return 0;
    const emp = props.employees.find(e => e.id === Number(empId));
    if (!emp) return 0;
    return type === 'base' ? Number(emp.base_hourly_rate) : Number(emp.overtime_hourly_rate);
};

const calcEmployeeRowTotal = (empRow: ActivityEmployeeForm) => {
    if (empRow.is_absent || !empRow.employee_id) return 0;
    const base = getEmployeeRate(empRow.employee_id, 'base');
    const ot = getEmployeeRate(empRow.employee_id, 'overtime');
    return ((Number(empRow.hours_worked) || 0) * base) + ((Number(empRow.overtime_hours) || 0) * ot);
};

// Nested Expenses methods
const addExpenseToActivity = (actIndex: number) => {
    form.activities[actIndex].expenses.push({
        concept: '',
        amount: '',
        payment_method_id: props.paymentMethods[0]?.id || '',
        payment_card_id: '',
        reference_number: '',
        notes: '',
    });
};

const removeExpenseFromActivity = (actIndex: number, expIndex: number) => {
    form.activities[actIndex].expenses.splice(expIndex, 1);
};

const isCashMethod = (methodId: number | string) => {
    const pm = props.paymentMethods.find(m => m.id === Number(methodId));
    if (!pm) return false;
    return pm.slug === 'efectivo' || pm.name.toLowerCase().includes('efectivo');
};

const getFilteredCards = (methodId: number | string) => {
    if (!methodId || isCashMethod(methodId)) return [];
    return props.paymentCards.filter(c => c.payment_method_id === Number(methodId));
};

const onPaymentMethodChange = (expRow: ActivityExpenseForm) => {
    if (isCashMethod(expRow.payment_method_id)) {
        expRow.payment_card_id = '';
    } else {
        const availableCards = getFilteredCards(expRow.payment_method_id);
        if (availableCards.length > 0) {
            expRow.payment_card_id = availableCards[0].id;
        } else {
            expRow.payment_card_id = '';
        }
    }
};

// Hours aggregation & validation per employee per date across all activities
const employeeHoursSummaryByDate = computed(() => {
    const map = new Map<string, { employeeName: string; date: string; hours: number; max: number; isExceeded: boolean }>();

    form.activities.forEach(act => {
        const dateStr = act.date;
        const max = getMaxHoursForDate(dateStr);

        act.employees.forEach(empRow => {
            if (empRow.is_absent || !empRow.employee_id) return;
            const key = `${empRow.employee_id}_${dateStr}`;
            const emp = props.employees.find(e => e.id === Number(empRow.employee_id));
            const name = emp ? `${emp.first_name} ${emp.last_name}` : `ID ${empRow.employee_id}`;

            const current = map.get(key) || { employeeName: name, date: dateStr, hours: 0, max, isExceeded: false };
            current.hours += Number(empRow.hours_worked) || 0;
            current.isExceeded = current.hours > max;
            map.set(key, current);
        });
    });

    return Array.from(map.values());
});

const hasHoursValidationErrors = computed(() => {
    return employeeHoursSummaryByDate.value.some(item => item.isExceeded);
});

// Grand Totals calculations
const totalPayroll = computed(() => {
    return form.activities.reduce((sum, act) => {
        return sum + act.employees.reduce((eSum, emp) => eSum + calcEmployeeRowTotal(emp), 0);
    }, 0);
});

const totalExpenses = computed(() => {
    return form.activities.reduce((sum, act) => {
        return sum + act.expenses.reduce((exSum, exp) => exSum + (Number(exp.amount) || 0), 0);
    }, 0);
});

const grandTotal = computed(() => totalPayroll.value + totalExpenses.value);

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(val);
};

const submit = () => {
    form.put(`/bitacoras/${props.bitacora.id}`);
};
</script>

<template>
    <Head :title="`Editar Bitácora ${bitacora.folio_number}`" />

    <div class="p-3 sm:p-6 lg:p-8 max-w-6xl mx-auto space-y-6 w-full min-w-0">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-4 sm:p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="flex items-center gap-3">
                <Link href="/bitacoras">
                    <Button variant="outline" size="icon" class="h-10 w-10 rounded-xl shrink-0">
                        <ArrowLeft class="h-5 w-5" />
                    </Button>
                </Link>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <ClipboardList class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600 dark:text-indigo-400" />
                            Bitácora {{ bitacora.folio_number }}
                        </h1>
                        <Badge class="bg-indigo-100 text-indigo-800 dark:bg-indigo-950/80 dark:text-indigo-300 font-mono text-xs">
                            {{ bitacora.branch?.name }}
                        </Badge>
                    </div>
                    <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Captura las actividades realizadas, personal asignado y gastos de cada actividad.
                    </p>
                </div>
            </div>

            <!-- Financial Summary Header Pills -->
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <div class="bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 px-3 py-1.5 rounded-xl text-left sm:text-right">
                    <span class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400 block">Total Bitácora</span>
                    <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-300 font-mono">{{ formatCurrency(grandTotal) }}</span>
                </div>
            </div>
        </div>

        <!-- General Info Banner -->
        <div class="bg-white dark:bg-zinc-900 p-4 sm:p-5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs sm:text-sm">
                <div>
                    <span class="text-zinc-400 block text-[11px] font-semibold uppercase">Cliente</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ bitacora.client?.name || 'Cliente General' }}</span>
                    <span class="text-zinc-400 block text-[11px] mt-0.5">
                        Sucursal: {{ bitacora.client_branch?.name || bitacora.clientBranch?.name || 'No Aplica / Matriz' }}
                    </span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px] font-semibold uppercase">Encargado Responsable</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ bitacora.user?.name || 'No asignado' }}</span>
                    <span class="text-zinc-400 block text-[11px] mt-0.5">{{ bitacora.user?.email }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px] font-semibold uppercase">Fecha Registro</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ bitacora.date }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px] font-semibold uppercase">Total Actividades</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ form.activities.length }} actividad(es)</span>
                </div>
            </div>

            <!-- Observaciones / Comentarios -->
            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                <div class="flex items-start gap-2.5">
                    <FileText class="h-4 w-4 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5" />
                    <div class="flex-1 min-w-0 space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 block">
                            Observaciones / Comentarios
                        </span>
                        <div v-if="isAdmin">
                            <textarea
                                v-model="form.notes"
                                rows="2"
                                placeholder="Escribe observaciones o comentarios generales para esta bitácora..."
                                class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 p-2.5 text-xs text-zinc-800 dark:text-zinc-200 focus:ring-2 focus:ring-indigo-500"
                            ></textarea>
                            <span v-if="form.errors.notes" class="text-xs text-red-500 font-medium">{{ form.errors.notes }}</span>
                        </div>
                        <div v-else>
                            <p v-if="form.notes || bitacora.notes" class="text-xs text-zinc-700 dark:text-zinc-300 whitespace-pre-line bg-zinc-50 dark:bg-zinc-800/40 p-2.5 rounded-xl border border-zinc-100 dark:border-zinc-800">
                                {{ form.notes || bitacora.notes }}
                            </p>
                            <p v-else class="text-xs text-zinc-400 italic">
                                Sin observaciones o comentarios registrados.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hours Exceeded Error Banner -->
        <div v-if="hasHoursValidationErrors" class="bg-red-50 dark:bg-red-950/50 border-2 border-red-300 dark:border-red-800 p-4 rounded-xl space-y-2">
            <div class="flex items-center gap-2 text-red-800 dark:text-red-300 font-bold text-sm">
                <ShieldAlert class="h-5 w-5 text-red-600 shrink-0" />
                <span>Atención: Límite de Horas Normales Excedido</span>
            </div>
            <ul class="text-xs text-red-700 dark:text-red-400 list-disc list-inside space-y-1">
                <li v-for="err in employeeHoursSummaryByDate.filter(e => e.isExceeded)" :key="`${err.employeeName}_${err.date}`">
                    <strong>{{ err.employeeName }}</strong> tiene <strong>{{ err.hours }} hrs</strong> normales asignadas el día <strong>{{ err.date }}</strong> (Máximo permitido: {{ err.max }} hrs).
                </li>
            </ul>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- ACTIVITIES SECTION (Cards Hierarchy) -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <ListCheck class="h-5 w-5 text-indigo-600" />
                            Actividades de la Bitácora
                        </h2>
                        <p class="text-xs text-zinc-500">Agrega las actividades realizadas con su respectivo personal y gastos.</p>
                    </div>
                    <Button type="button" @click="addActivity" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow text-xs">
                        <Plus class="h-4 w-4 mr-1.5" /> Agregar Actividad
                    </Button>
                </div>

                <!-- Activity Card Loop -->
                <div
                    v-for="(act, actIndex) in form.activities"
                    :key="actIndex"
                    class="bg-white dark:bg-zinc-900 rounded-2xl border-2 transition-all shadow-sm overflow-hidden"
                    :class="isSundayDate(act.date)
                        ? 'border-amber-400 dark:border-amber-600 bg-amber-50/10'
                        : 'border-zinc-200 dark:border-zinc-800'"
                >
                    <!-- Activity Card Top Header -->
                    <div
                        class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                        :class="isSundayDate(act.date)
                            ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800/80'
                            : 'bg-zinc-50 dark:bg-zinc-800/50 border-zinc-200 dark:border-zinc-800'"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center h-8 w-8 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow">
                                #{{ actIndex + 1 }}
                            </span>
                            <div>
                                <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                    Actividad #{{ actIndex + 1 }}
                                    <Badge v-if="isSundayDate(act.date)" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold text-[11px] gap-1 shadow-sm">
                                        <Sun class="h-3.5 w-3.5" /> Actividad en Domingo
                                    </Badge>
                                </h3>
                                <span class="text-[11px] text-zinc-500">
                                    {{ isSaturdayDate(act.date) ? 'Día Sábado (Máx 6h normales)' : (isSundayDate(act.date) ? 'Día Domingo' : 'Entre Semana (Máx 8h normales)') }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Activity Date in Header Row -->
                            <div class="flex items-center gap-2">
                                <div class="w-36">
                                    <Input
                                        type="date"
                                        v-model="act.date"
                                        required
                                        class="h-8 text-xs rounded-lg font-mono"
                                    />
                                </div>
                            </div>

                            <Button
                                v-if="form.activities.length > 1"
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg"
                                @click="removeActivity(actIndex)"
                                title="Eliminar actividad completa"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Activity Body -->
                    <div class="p-4 sm:p-6 space-y-6">
                        <!-- Activity Description Field -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Descripción Detallada de la Actividad *
                            </Label>
                            <Input
                                v-model="act.description"
                                placeholder="Ej: Reparación de tubería hidráulica y soldadura en área de producción..."
                                required
                                class="text-xs h-9 rounded-xl"
                            />
                        </div>

                        <!-- SUBSECTION: EMPLOYEES OF THIS ACTIVITY -->
                        <div class="bg-zinc-50/70 dark:bg-zinc-800/30 p-4 rounded-xl border border-zinc-200/80 dark:border-zinc-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                    <Users class="h-4 w-4 text-indigo-600" />
                                    Personal Asignado a esta Actividad ({{ act.employees.length }})
                                </span>
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    @click="addEmployeeToActivity(actIndex)"
                                    class="h-7 text-xs rounded-lg border-indigo-200 text-indigo-600 hover:bg-indigo-50"
                                >
                                    <Plus class="h-3 w-3 mr-1" /> Asignar Empleado
                                </Button>
                            </div>

                            <!-- Employees Table -->
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-xs text-left min-w-[550px]">
                                    <thead class="text-[11px] uppercase bg-zinc-200/60 dark:bg-zinc-800 text-zinc-600 font-semibold">
                                        <tr>
                                            <th class="py-2 px-3">Empleado</th>
                                            <th class="py-2 px-3 text-center">Estado</th>
                                            <th class="py-2 px-3 text-center">Horas Normales</th>
                                            <th class="py-2 px-3 text-center">Horas Extras</th>
                                            <th class="py-2 px-3 text-right">Subtotal</th>
                                            <th class="py-2 px-2 text-center w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                        <tr v-for="(empRow, empIndex) in act.employees" :key="empIndex" :class="empRow.is_absent ? 'bg-amber-50/40 dark:bg-amber-950/20' : ''">
                                            <!-- Employee Select -->
                                            <td class="py-2 px-3 min-w-[200px]">
                                                <select
                                                    v-model="empRow.employee_id"
                                                    required
                                                    class="w-full h-8 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 text-xs"
                                                >
                                                    <option value="" disabled>Selecciona un empleado</option>
                                                    <option v-for="e in employees" :key="e.id" :value="e.id">
                                                        {{ e.first_name }} {{ e.last_name }} [{{ e.employee_code }}]
                                                    </option>
                                                </select>
                                            </td>

                                            <!-- Absent Toggle Button -->
                                            <td class="py-2 px-3 text-center min-w-[120px]">
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    :variant="empRow.is_absent ? 'destructive' : 'secondary'"
                                                    class="h-7 text-[11px] rounded-lg w-full"
                                                    @click="toggleAbsent(empRow)"
                                                >
                                                    {{ empRow.is_absent ? '⚠️ Falta' : '✅ Asistió' }}
                                                </Button>
                                            </td>

                                            <!-- Normal Hours -->
                                            <td class="py-2 px-3 text-center min-w-[100px]">
                                                <Input
                                                    type="number"
                                                    step="0.5"
                                                    min="0"
                                                    :max="getMaxHoursForDate(act.date)"
                                                    v-model.number="empRow.hours_worked"
                                                    :disabled="empRow.is_absent"
                                                    class="h-8 text-center text-xs font-mono rounded-lg"
                                                />
                                            </td>

                                            <!-- Overtime Hours -->
                                            <td class="py-2 px-3 text-center min-w-[100px]">
                                                <Input
                                                    type="number"
                                                    step="0.5"
                                                    min="0"
                                                    v-model.number="empRow.overtime_hours"
                                                    :disabled="empRow.is_absent"
                                                    class="h-8 text-center text-xs font-mono rounded-lg"
                                                />
                                            </td>

                                            <!-- Subtotal -->
                                            <td class="py-2 px-3 text-right font-mono font-bold text-zinc-900 dark:text-zinc-100 min-w-[100px]">
                                                {{ formatCurrency(calcEmployeeRowTotal(empRow)) }}
                                            </td>

                                            <!-- Remove Action -->
                                            <td class="py-2 px-2 text-center">
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-7 w-7 text-zinc-400 hover:text-red-600 rounded-md"
                                                    @click="removeEmployeeFromActivity(actIndex, empIndex)"
                                                >
                                                    <Trash2 class="h-3.5 w-3.5" />
                                                </Button>
                                            </td>
                                        </tr>

                                        <tr v-if="act.employees.length === 0">
                                            <td colspan="6" class="py-3 text-center text-zinc-400 italic">
                                                No hay personal asignado a esta actividad aún.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- SUBSECTION: EXPENSES OF THIS ACTIVITY -->
                        <div class="bg-zinc-50/70 dark:bg-zinc-800/30 p-4 rounded-xl border border-zinc-200/80 dark:border-zinc-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                    <Receipt class="h-4 w-4 text-emerald-600" />
                                    Gastos Asociados a esta Actividad ({{ act.expenses.length }})
                                </span>
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    @click="addExpenseToActivity(actIndex)"
                                    class="h-7 text-xs rounded-lg border-emerald-200 text-emerald-600 hover:bg-emerald-50"
                                >
                                    <Plus class="h-3 w-3 mr-1" /> Agregar Gasto
                                </Button>
                            </div>

                            <!-- Expenses Table -->
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-xs text-left min-w-[550px]">
                                    <thead class="text-[11px] uppercase bg-zinc-200/60 dark:bg-zinc-800 text-zinc-600 font-semibold">
                                        <tr>
                                            <th class="py-2 px-3">Concepto</th>
                                            <th class="py-2 px-3">Monto ($)</th>
                                            <th class="py-2 px-3">Método de Pago</th>
                                            <th class="py-2 px-3">Tarjeta / Cuenta Registrada</th>
                                            <th class="py-2 px-3">Referencia</th>
                                            <th class="py-2 px-2 text-center w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                        <tr v-for="(expRow, expIndex) in act.expenses" :key="expIndex">
                                            <!-- Concept -->
                                            <td class="py-2 px-3 min-w-[160px]">
                                                <Input
                                                    v-model="expRow.concept"
                                                    placeholder="Ej: Materiales, Gasolina..."
                                                    required
                                                    class="h-8 text-xs rounded-lg"
                                                />
                                            </td>

                                            <!-- Amount -->
                                            <td class="py-2 px-3 min-w-[110px]">
                                                <Input
                                                    type="number"
                                                    step="0.01"
                                                    min="0.01"
                                                    v-model.number="expRow.amount"
                                                    placeholder="0.00"
                                                    required
                                                    class="h-8 text-xs font-mono rounded-lg"
                                                />
                                            </td>

                                            <!-- Payment Method -->
                                            <td class="py-2 px-3 min-w-[140px]">
                                                <select
                                                    v-model="expRow.payment_method_id"
                                                    @change="onPaymentMethodChange(expRow)"
                                                    required
                                                    class="w-full h-8 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 text-xs"
                                                >
                                                    <option value="" disabled>Método...</option>
                                                    <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.id">{{ pm.name }}</option>
                                                </select>
                                            </td>

                                            <!-- Payment Card -->
                                            <td class="py-2 px-3 min-w-[180px]">
                                                <select
                                                    v-model="expRow.payment_card_id"
                                                    :disabled="isCashMethod(expRow.payment_method_id)"
                                                    class="w-full h-8 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 text-xs"
                                                    :class="isCashMethod(expRow.payment_method_id) ? 'opacity-50 cursor-not-allowed' : ''"
                                                >
                                                    <option value="">{{ isCashMethod(expRow.payment_method_id) ? 'No aplica (Efectivo)' : 'Seleccionar cuenta/tarjeta...' }}</option>
                                                    <option v-for="c in getFilteredCards(expRow.payment_method_id)" :key="c.id" :value="c.id">
                                                        {{ c.alias }} ({{ c.card_number_masked }})
                                                    </option>
                                                </select>
                                            </td>

                                            <!-- Reference Number -->
                                            <td class="py-2 px-3 min-w-[120px]">
                                                <Input
                                                    v-model="expRow.reference_number"
                                                    placeholder="Ticket / Folio"
                                                    class="h-8 text-xs rounded-lg font-mono"
                                                />
                                            </td>

                                            <!-- Remove Action -->
                                            <td class="py-2 px-2 text-center">
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-7 w-7 text-zinc-400 hover:text-red-600 rounded-md"
                                                    @click="removeExpenseFromActivity(actIndex, expIndex)"
                                                >
                                                    <Trash2 class="h-3.5 w-3.5" />
                                                </Button>
                                            </td>
                                        </tr>

                                        <tr v-if="act.expenses.length === 0">
                                            <td colspan="6" class="py-3 text-center text-zinc-400 italic">
                                                No hay gastos registrados para esta actividad.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            <Card class="border-zinc-200 dark:border-zinc-800 shadow-sm">
                <CardHeader class="py-3 px-6 border-b border-zinc-100 dark:border-zinc-800">
                    <CardTitle class="text-sm font-semibold">Notas u Observaciones Finales</CardTitle>
                </CardHeader>
                <CardContent class="p-4">
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Observaciones adicionales sobre la ejecución de la bitácora..."
                        class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3 text-xs focus:ring-2 focus:ring-indigo-500"
                    ></textarea>
                </CardContent>
            </Card>

            <!-- Bottom Floating Action Bar -->
            <div class="sticky bottom-4 z-20 bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-6 text-xs sm:text-sm">
                    <div>
                        <span class="text-zinc-400 block text-[10px] font-bold uppercase">Total Nómina</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 font-mono">{{ formatCurrency(totalPayroll) }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block text-[10px] font-bold uppercase">Total Gastos</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 font-mono">{{ formatCurrency(totalExpenses) }}</span>
                    </div>
                    <div class="border-l border-zinc-200 dark:border-zinc-700 pl-6">
                        <span class="text-emerald-600 dark:text-emerald-400 block text-[10px] font-bold uppercase">Gran Total</span>
                        <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">{{ formatCurrency(grandTotal) }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <Link href="/bitacoras" class="w-full sm:w-auto">
                        <Button type="button" variant="outline" class="w-full sm:w-auto rounded-xl text-xs">
                            Cancelar
                        </Button>
                    </Link>
                    <Button
                        type="submit"
                        :disabled="form.processing || hasHoursValidationErrors"
                        class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow px-6 text-xs flex items-center justify-center gap-2"
                    >
                        <Save class="h-4 w-4" />
                        {{ form.processing ? 'Guardando Cambios...' : 'Guardar y Finalizar' }}
                    </Button>
                </div>
            </div>
        </form>
    </div>
</template>
