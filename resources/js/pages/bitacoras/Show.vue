<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    ArrowLeft,
    Printer,
    Edit,
    Building2,
    Calendar,
    UserCheck,
    Users,
    Receipt,
    CreditCard,
    Sun,
    ListCheck,
    FileText
} from '@lucide/vue';

interface Bitacora {
    id: number;
    folio_number: string;
    date: string;
    notes: string | null;
    branch: { name: string; address?: string; phone?: string };
    user?: { name: string; email: string };
    client?: { name: string; code: string; contact_name?: string; phone?: string };
    client_branch?: { name: string; code?: string; address?: string };
    clientBranch?: { name: string; code?: string; address?: string };
    activities: {
        id: number;
        date: string;
        description: string;
        activity_type?: { name: string };
        activityType?: { name: string };
        employees?: {
            id: number;
            employee_id: number;
            is_absent: boolean;
            hours_worked: number;
            overtime_hours: number;
            base_rate_applied: number;
            overtime_rate_applied: number;
            total_earned: number;
            employee?: { first_name: string; last_name: string; employee_code: string };
        }[];
        expenses?: {
            id: number;
            concept: string;
            amount: number;
            reference_number: string | null;
            notes: string | null;
            payment_method?: { name: string };
            paymentMethod?: { name: string };
            payment_card?: { alias: string; card_number_masked: string; bank_name?: string };
            paymentCard?: { alias: string; card_number_masked: string; bank_name?: string };
        }[];
    }[];
}

const props = defineProps<{
    bitacora: Bitacora;
}>();

const formatCurrency = (val: number | string) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(val) || 0);
};

const isSundayDate = (dateStr: string): boolean => {
    if (!dateStr) return false;
    const d = new Date(dateStr + 'T00:00:00');
    return d.getDay() === 0;
};

// Calculate totals across all activities
const totalPayroll = (props.bitacora.activities || []).reduce((sum, act) => {
    return sum + (act.employees || []).reduce((eSum, emp) => eSum + (Number(emp.total_earned) || 0), 0);
}, 0);

const totalExpenses = (props.bitacora.activities || []).reduce((sum, act) => {
    return sum + (act.expenses || []).reduce((exSum, exp) => exSum + (Number(exp.amount) || 0), 0);
}, 0);

const grandTotal = totalPayroll + totalExpenses;

const print = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Bitácora ${bitacora.folio_number}`" />

    <div class="p-3 sm:p-6 lg:p-8 max-w-5xl mx-auto space-y-6 w-full min-w-0 print:p-0 print:space-y-4">
        <!-- Actions Top Bar (Hidden on Print) -->
        <div class="flex flex-wrap items-center justify-between gap-2 print:hidden">
            <Link href="/bitacoras">
                <Button variant="outline" size="sm" class="rounded-xl">
                    <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver al Listado
                </Button>
            </Link>

            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="print" class="rounded-xl">
                    <Printer class="h-4 w-4 mr-1.5" /> Imprimir
                </Button>
                <Link :href="`/bitacoras/${bitacora.id}/edit`">
                    <Button size="sm" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow">
                        <Edit class="h-4 w-4 mr-1.5" /> Editar Bitácora
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Main Printable Sheet -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-sm overflow-hidden p-4 sm:p-8 space-y-6 print:border-none print:shadow-none print:p-0 w-full min-w-0">
            <!-- Header Brand & Folio -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 block mb-1">
                        Reporte Operativo de Bitácora
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ bitacora.folio_number }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <Badge class="bg-indigo-100 text-indigo-800 dark:bg-indigo-950/80 dark:text-indigo-300 font-mono text-sm px-3 py-1">
                        {{ bitacora.branch?.name }}
                    </Badge>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="p-4 sm:p-5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-800 space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <span class="text-zinc-400 uppercase font-semibold text-[10px] block">Cliente</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ bitacora.client?.name || 'Cliente General' }}</span>
                        <span v-if="bitacora.client?.code" class="text-zinc-400 font-mono">[{{ bitacora.client.code }}]</span>
                    </div>

                    <div>
                        <span class="text-zinc-400 uppercase font-semibold text-[10px] block">Sucursal de Cliente</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 block">
                            {{ bitacora.client_branch?.name || bitacora.clientBranch?.name || 'No Aplica (Matriz)' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-zinc-400 uppercase font-semibold text-[10px] block">Encargado Responsable</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ bitacora.user?.name || 'No asignado' }}</span>
                        <span class="text-zinc-400">{{ bitacora.user?.email }}</span>
                    </div>

                    <div>
                        <span class="text-zinc-400 uppercase font-semibold text-[10px] block">Fecha de Registro</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 font-mono block">{{ bitacora.date }}</span>
                    </div>
                </div>

                <!-- Notas u Observaciones Generales -->
                <div class="pt-3 border-t border-zinc-200/80 dark:border-zinc-700/60">
                    <span class="text-zinc-400 uppercase font-semibold text-[10px] block">
                        Notas u Observaciones Generales
                    </span>
                    <p v-if="bitacora.notes" class="text-zinc-800 dark:text-zinc-200 text-xs sm:text-sm whitespace-pre-line mt-1 font-medium">
                        {{ bitacora.notes }}
                    </p>
                    <p v-else class="text-zinc-400 text-xs italic mt-1">
                        Sin observaciones registradas.
                    </p>
                </div>
            </div>

            <!-- ACTIVITIES & DETAILS SECTION -->
            <div class="space-y-6">
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                    <ListCheck class="h-5 w-5 text-indigo-600" />
                    Actividades Realizadas ({{ bitacora.activities?.length || 0 }})
                </h2>

                <!-- Activity Card Loop -->
                <div
                    v-for="(act, actIndex) in bitacora.activities"
                    :key="act.id"
                    class="border rounded-2xl overflow-hidden shadow-xs"
                    :class="isSundayDate(act.date)
                        ? 'border-amber-300 dark:border-amber-700 bg-amber-50/20'
                        : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'"
                >
                    <!-- Activity Header -->
                    <div
                        class="p-4 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-2"
                        :class="isSundayDate(act.date)
                            ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800'
                            : 'bg-zinc-50 dark:bg-zinc-800/60 border-zinc-200 dark:border-zinc-800'"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center h-7 w-7 rounded-lg bg-indigo-600 text-white font-bold text-xs">
                                #{{ actIndex + 1 }}
                            </span>
                            <div>
                                <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                    Actividad #{{ actIndex + 1 }}
                                </span>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">{{ act.description }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <Badge v-if="isSundayDate(act.date)" class="bg-amber-500 text-white font-semibold text-[11px] gap-1">
                                <Sun class="h-3.5 w-3.5" /> Actividad en Domingo
                            </Badge>
                            <span class="text-xs font-mono font-semibold text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 px-2.5 py-1 rounded-md border border-zinc-200 dark:border-zinc-700">
                                {{ act.date }}
                            </span>
                        </div>
                    </div>

                    <!-- Activity Children (Employees + Expenses) -->
                    <div class="p-4 sm:p-5 space-y-4">
                        <!-- Employees Table -->
                        <div>
                            <span class="text-[11px] font-bold uppercase text-zinc-500 block mb-2">Personal Asignado</span>
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-xs text-left min-w-[500px]">
                                    <thead class="bg-zinc-100 dark:bg-zinc-800 text-zinc-500 uppercase text-[10px]">
                                        <tr>
                                            <th class="py-1.5 px-3">Empleado</th>
                                            <th class="py-1.5 px-3 text-center">Estado</th>
                                            <th class="py-1.5 px-3 text-center">Horas Normales</th>
                                            <th class="py-1.5 px-3 text-center">Horas Extras</th>
                                            <th class="py-1.5 px-3 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                        <tr v-for="emp in act.employees" :key="emp.id">
                                            <td class="py-2 px-3 font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ emp.employee?.first_name }} {{ emp.employee?.last_name }}
                                                <span class="text-zinc-400 font-mono text-[10px]">[{{ emp.employee?.employee_code }}]</span>
                                            </td>
                                            <td class="py-2 px-3 text-center">
                                                <Badge v-if="emp.is_absent" variant="destructive" class="text-[10px] py-0">
                                                    ⚠️ Falta
                                                </Badge>
                                                <div v-else-if="emp.is_partial_shift" class="flex flex-col items-center gap-0.5">
                                                    <Badge class="bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-300 text-[10px] py-0">
                                                        ⏱️ Solo {{ emp.hours_worked }}h (Parcial)
                                                    </Badge>
                                                    <span v-if="emp.partial_shift_reason" class="text-[9px] text-blue-600 dark:text-blue-400 italic">
                                                        {{ emp.partial_shift_reason }}
                                                    </span>
                                                </div>
                                                <Badge v-else variant="secondary" class="text-[10px] py-0">
                                                    ✅ Asistió
                                                </Badge>
                                            </td>
                                            <td class="py-2 px-3 text-center font-mono">{{ emp.is_absent ? '-' : `${emp.hours_worked} hrs` }}</td>
                                            <td class="py-2 px-3 text-center font-mono">{{ emp.is_absent ? '-' : `${emp.overtime_hours} hrs` }}</td>
                                            <td class="py-2 px-3 text-right font-mono font-bold">{{ formatCurrency(emp.total_earned) }}</td>
                                        </tr>
                                        <tr v-if="!act.employees || act.employees.length === 0">
                                            <td colspan="5" class="py-2 text-center text-zinc-400 italic">No se asignó personal.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Expenses Table -->
                        <div v-if="act.expenses && act.expenses.length > 0" class="pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <span class="text-[11px] font-bold uppercase text-emerald-700 dark:text-emerald-400 block mb-2">Gastos de la Actividad</span>
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-xs text-left min-w-[500px]">
                                    <thead class="bg-emerald-50/50 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-300 uppercase text-[10px]">
                                        <tr>
                                            <th class="py-1.5 px-3">Concepto</th>
                                            <th class="py-1.5 px-3">Método de Pago</th>
                                            <th class="py-1.5 px-3">Cuenta / Tarjeta</th>
                                            <th class="py-1.5 px-3">Referencia</th>
                                            <th class="py-1.5 px-3 text-right">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                        <tr v-for="exp in act.expenses" :key="exp.id">
                                            <td class="py-2 px-3 font-semibold text-zinc-900 dark:text-zinc-100">{{ exp.concept }}</td>
                                            <td class="py-2 px-3">{{ exp.payment_method?.name || exp.paymentMethod?.name || '-' }}</td>
                                            <td class="py-2 px-3">
                                                <span v-if="exp.payment_card || exp.paymentCard">
                                                    {{ (exp.payment_card || exp.paymentCard)?.alias }} ({{ (exp.payment_card || exp.paymentCard)?.card_number_masked }})
                                                </span>
                                                <span v-else class="text-zinc-400">Efectivo / N/A</span>
                                            </td>
                                            <td class="py-2 px-3 font-mono text-zinc-500">{{ exp.reference_number || '-' }}</td>
                                            <td class="py-2 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ formatCurrency(exp.amount) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div v-if="bitacora.notes" class="p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-800 text-xs">
                <span class="text-zinc-400 uppercase font-semibold text-[10px] block mb-1">Notas u Observaciones</span>
                <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line">{{ bitacora.notes }}</p>
            </div>

            <!-- Financial Summary Box -->
            <div class="border-t-2 border-zinc-200 dark:border-zinc-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-6 text-xs sm:text-sm">
                    <div>
                        <span class="text-zinc-400 text-[10px] uppercase font-bold block">Total Nómina</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 font-mono">{{ formatCurrency(totalPayroll) }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 text-[10px] uppercase font-bold block">Total Gastos</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100 font-mono">{{ formatCurrency(totalExpenses) }}</span>
                    </div>
                </div>

                <div class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700 px-6 py-3 rounded-2xl text-right w-full sm:w-auto">
                    <span class="text-xs uppercase font-bold text-emerald-800 dark:text-emerald-300 block">Costo Total de Bitácora</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">{{ formatCurrency(grandTotal) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
