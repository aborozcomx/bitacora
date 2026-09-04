<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Receipt,
    Calendar,
    Building,
    CreditCard,
    DollarSign,
    Wallet,
    Landmark,
    FileSpreadsheet,
    ChevronLeft,
    ChevronRight,
    ExternalLink
} from '@lucide/vue';

interface Branch { id: number; name: string; }
interface PaymentMethod {
    id: number;
    name: string;
    slug?: string;
    requires_card_details?: boolean;
}
interface PaymentCard {
    id: number;
    alias: string;
    bank_name?: string;
    card_number_masked: string;
    payment_method_id: number;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface ExpenseItem {
    id: number;
    concept: string;
    amount: number;
    date: string | null;
    reference_number: string | null;
    notes: string | null;
    created_at: string;
    bitacora: {
        id: number;
        folio_number: string;
        date: string;
        branch?: { name: string };
        user?: { name: string };
    };
    payment_method?: { name: string; slug?: string };
    paymentMethod?: { name: string; slug?: string };
    card_type?: { name: string };
    cardType?: { name: string };
    payment_card?: { alias: string; bank_name?: string; card_number_masked: string };
    paymentCard?: { alias: string; bank_name?: string; card_number_masked: string };
}

interface GroupedMethod {
    payment_method_id: number;
    total_amount: number;
    total_count: number;
    payment_method?: { name: string };
    paymentMethod?: { name: string };
}

const props = defineProps<{
    expenses: {
        data: ExpenseItem[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    byPaymentMethod: GroupedMethod[];
    branches: Branch[];
    users: User[];
    paymentMethods: PaymentMethod[];
    paymentCards: PaymentCard[];
    filters: {
        start_date: string;
        end_date: string;
        branch_id: string | null;
        user_id: string | null;
        payment_method_id: string | null;
        payment_card_id: string | null;
    };
    grand_total: number;
    total_transactions: number;
}>();

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const branchId = ref(props.filters.branch_id || '');
const userId = ref(props.filters.user_id || '');
const paymentMethodId = ref(props.filters.payment_method_id || '');
const paymentCardId = ref(props.filters.payment_card_id || '');

// Filter cards based on selected payment method
const filteredPaymentCards = computed(() => {
    if (!paymentMethodId.value) {
        return props.paymentCards || [];
    }
    return (props.paymentCards || []).filter(
        c => c.payment_method_id === Number(paymentMethodId.value)
    );
});

// Check if selected method is Cash (only Cash disables card/account filter)
const isCashSelected = computed(() => {
    if (!paymentMethodId.value) return false;
    const pm = props.paymentMethods.find(m => m.id === Number(paymentMethodId.value));
    if (!pm) return false;
    return pm.slug === 'efectivo' || pm.name.toLowerCase().includes('efectivo');
});

const onPaymentMethodChange = () => {
    // If payment method changed and current card doesn't belong to it, reset card filter
    if (paymentCardId.value) {
        const cardExistsInFiltered = filteredPaymentCards.value.some(c => c.id === Number(paymentCardId.value));
        if (!cardExistsInFiltered) {
            paymentCardId.value = '';
        }
    }
    handleFilter();
};

const handleFilter = () => {
    router.get('/expenses', {
        start_date: startDate.value,
        end_date: endDate.value,
        branch_id: branchId.value,
        user_id: userId.value,
        payment_method_id: paymentMethodId.value,
        payment_card_id: paymentCardId.value,
    }, { preserveState: true, replace: true });
};

// Weekly period calculation (Wednesday to following Thursday)
const getWeeklyPeriods = () => {
    const periods = [];
    const today = new Date();
    const currentDay = today.getDay(); // 0: Sun, 1: Mon, 2: Tue, 3: Wed, 4: Thu, 5: Fri, 6: Sat
    const diffToWed = (currentDay < 3 ? currentDay + 7 : currentDay) - 3;
    
    const baseWed = new Date(today);
    baseWed.setDate(today.getDate() - diffToWed);

    for (let i = 2; i >= -8; i--) {
        const wed = new Date(baseWed);
        wed.setDate(baseWed.getDate() + (i * 7));
        const thu = new Date(wed);
        thu.setDate(wed.getDate() + 8);

        const startStr = wed.toISOString().substring(0, 10);
        const endStr = thu.toISOString().substring(0, 10);
        
        let label = `Semana: Mié ${startStr} al Jue ${endStr}`;
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

    startDate.value = currentStart.toISOString().substring(0, 10);
    endDate.value = currentEnd.toISOString().substring(0, 10);
    handleFilter();
};

const applyCurrentWeek = () => {
    const today = new Date();
    const currentDay = today.getDay();
    const diffToWed = (currentDay < 3 ? currentDay + 7 : currentDay) - 3;
    const wed = new Date(today);
    wed.setDate(today.getDate() - diffToWed);
    const thu = new Date(wed);
    thu.setDate(wed.getDate() + 8);

    startDate.value = wed.toISOString().substring(0, 10);
    endDate.value = thu.toISOString().substring(0, 10);
    handleFilter();
};

const applyNextWeek = () => {
    const currentStart = new Date(startDate.value + 'T00:00:00');
    currentStart.setDate(currentStart.getDate() + 7);
    const currentEnd = new Date(endDate.value + 'T00:00:00');
    currentEnd.setDate(currentEnd.getDate() + 7);

    startDate.value = currentStart.toISOString().substring(0, 10);
    endDate.value = currentEnd.toISOString().substring(0, 10);
    handleFilter();
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="Reporte de Gastos" />

    <div class="p-6 space-y-6 max-w-7xl mx-auto print:p-0 print:max-w-none">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm print:hidden">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <Receipt class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                    Reporte Semanal de Gastos por Método y Tarjeta
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Consolidado semanal de egresos (ciclo <strong>Miércoles a Jueves</strong>) desglosados por transferencia, efectivo y tarjeta registrada.
                </p>
            </div>
            <Button variant="outline" @click="printReport" class="rounded-xl">
                <FileSpreadsheet class="h-4 w-4 mr-2" /> Imprimir Reporte
            </Button>
        </div>

        <!-- Weekly Filter Quick Controls -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3 print:hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase text-amber-600 dark:text-amber-400 tracking-wider flex items-center gap-1.5">
                        <Calendar class="h-4 w-4" /> Período Semanal (Miércoles a Jueves):
                    </span>
                    <span class="text-xs font-mono font-semibold text-zinc-800 dark:text-zinc-200 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-1 rounded-md border border-amber-200 dark:border-amber-800">
                        {{ startDate }} al {{ endDate }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="applyPreviousWeek" class="rounded-lg text-xs">
                        <ChevronLeft class="h-3.5 w-3.5 mr-1" /> Semana Anterior
                    </Button>
                    <Button variant="secondary" size="sm" @click="applyCurrentWeek" class="rounded-lg text-xs font-semibold">
                        Semana Actual (Mié - Jue)
                    </Button>
                    <Button variant="outline" size="sm" @click="applyNextWeek" class="rounded-lg text-xs">
                        Semana Siguiente <ChevronRight class="h-3.5 w-3.5 ml-1" />
                    </Button>
                </div>
            </div>

            <!-- Filters Form Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 pt-1">
                <div>
                    <label class="text-xs font-semibold text-zinc-500 block mb-1">Selector de Semana</label>
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
                    <label class="text-xs font-semibold text-zinc-500 block mb-1">Método de Pago</label>
                    <select
                        v-model="paymentMethodId"
                        @change="onPaymentMethodChange"
                        class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-3 text-xs"
                    >
                        <option value="">Todos los Métodos</option>
                        <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.id">{{ pm.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-zinc-500 block mb-1">Cuenta / Tarjeta Registrada</label>
                    <select
                        v-model="paymentCardId"
                        @change="handleFilter"
                        :disabled="isCashSelected"
                        class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-3 text-xs"
                        :class="isCashSelected ? 'opacity-50 cursor-not-allowed' : ''"
                    >
                        <option value="">{{ filteredPaymentCards.length > 0 ? 'Todas las Tarjetas / Cuentas' : 'Sin cuentas/tarjetas registradas' }}</option>
                        <option
                            v-for="c in filteredPaymentCards"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.alias }} ({{ c.card_number_masked }}) {{ c.bank_name ? `- ${c.bank_name}` : '' }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- CARDS SUMMARY BY METHOD -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <Card class="rounded-2xl border-amber-200 dark:border-amber-900/60 shadow-sm bg-amber-50/40 dark:bg-amber-950/20">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-medium uppercase text-amber-700 dark:text-amber-300">Total General Egresos</CardTitle>
                    <DollarSign class="h-4 w-4 text-amber-600" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 font-mono">
                        ${{ grand_total.toFixed(2) }}
                    </div>
                    <p class="text-xs text-zinc-500 mt-1 font-medium">
                        {{ total_transactions }} transacciones en el período
                    </p>
                </CardContent>
            </Card>

            <Card v-for="group in byPaymentMethod" :key="group.payment_method_id" class="rounded-2xl border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-medium uppercase text-zinc-500">
                        {{ group.payment_method?.name || group.paymentMethod?.name || 'Método no especificado' }}
                    </CardTitle>
                    <Wallet class="h-4 w-4 text-zinc-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        ${{ Number(group.total_amount).toFixed(2) }}
                    </div>
                    <p class="text-xs text-zinc-400 mt-1 font-medium">
                        {{ group.total_count }} {{ group.total_count === 1 ? 'transacción' : 'transacciones' }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- EXPENSES TABLE -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
                <h2 class="font-bold text-zinc-900 dark:text-zinc-100 text-base">
                    Listado Detallado de Gastos por Folio ({{ startDate }} al {{ endDate }})
                </h2>
                <span class="text-xs text-zinc-500">
                    Total: <strong class="font-mono text-amber-600">${{ grand_total.toFixed(2) }}</strong>
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-300">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-xs font-semibold text-zinc-500 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-4">Folio / Fecha</th>
                            <th class="py-3 px-4">Sucursal</th>
                            <th class="py-3 px-4">Encargado</th>
                            <th class="py-3 px-4">Concepto de Gasto</th>
                            <th class="py-3 px-4">Método de Pago</th>
                            <th class="py-3 px-4">Tarjeta / Cuenta Registrada</th>
                            <th class="py-3 px-4">Referencia</th>
                            <th class="py-3 px-4 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <tr
                            v-for="ex in expenses.data"
                            :key="ex.id"
                            class="transition"
                            :class="(ex.date || ex.bitacora?.date) && new Date((ex.date || ex.bitacora?.date) + 'T00:00:00').getDay() === 0
                                ? 'bg-amber-50/70 dark:bg-amber-950/30 hover:bg-amber-100/70 dark:hover:bg-amber-900/40'
                                : 'hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40'"
                        >
                            <td class="py-3.5 px-4">
                                <Link
                                    v-if="ex.bitacora"
                                    :href="`/bitacoras/${ex.bitacora.id}`"
                                    class="font-mono font-semibold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1"
                                >
                                    {{ ex.bitacora.folio_number }} <ExternalLink class="h-3 w-3" />
                                </Link>
                                <span v-else class="font-mono font-semibold text-zinc-500">-</span>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-1 mt-0.5 font-mono">
                                    <Calendar class="h-3 w-3" /> {{ ex.date || ex.bitacora?.date }}
                                    <Badge
                                        v-if="(ex.date || ex.bitacora?.date) && new Date((ex.date || ex.bitacora?.date) + 'T00:00:00').getDay() === 0"
                                        class="bg-amber-500 text-white font-semibold text-[9px] py-0 px-1 ml-1"
                                    >
                                        ☀️ Domingo
                                    </Badge>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-zinc-900 dark:text-zinc-100">
                                {{ ex.bitacora?.branch?.name || 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-zinc-600 dark:text-zinc-400">
                                {{ ex.bitacora?.user?.name || '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-zinc-900 dark:text-zinc-100">
                                {{ ex.concept }}
                                <p v-if="ex.notes" class="text-xs text-zinc-400 font-normal mt-0.5">{{ ex.notes }}</p>
                            </td>
                            <td class="py-3.5 px-4">
                                <Badge variant="outline" class="font-medium bg-zinc-50 dark:bg-zinc-800">
                                    {{ ex.payment_method?.name || ex.paymentMethod?.name || 'No especificado' }}
                                </Badge>
                            </td>
                            <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300">
                                <div v-if="ex.payment_card || ex.paymentCard" class="flex flex-col">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ (ex.payment_card || ex.paymentCard)?.alias }}
                                    </span>
                                    <span class="text-xs font-mono text-zinc-400">
                                        {{ (ex.payment_card || ex.paymentCard)?.card_number_masked }} {{ (ex.payment_card || ex.paymentCard)?.bank_name ? `• ${(ex.payment_card || ex.paymentCard)?.bank_name}` : '' }}
                                    </span>
                                </div>
                                <span v-else-if="ex.card_type || ex.cardType" class="text-zinc-600">
                                    {{ (ex.card_type || ex.cardType)?.name }}
                                </span>
                                <span v-else-if="(ex.payment_method?.slug || ex.paymentMethod?.slug) === 'efectivo' || (ex.payment_method?.name || ex.paymentMethod?.name)?.toLowerCase().includes('efectivo')" class="text-xs text-emerald-600 font-medium">
                                    Efectivo
                                </span>
                                <span v-else class="italic text-zinc-400">-</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-zinc-500">
                                {{ ex.reference_number || '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-amber-600 dark:text-amber-400 font-mono text-base">
                                ${{ Number(ex.amount).toFixed(2) }}
                            </td>
                        </tr>
                        <tr v-if="expenses.data.length === 0">
                            <td colspan="8" class="py-10 text-center text-zinc-400">
                                No hay gastos registrados en el período y filtros seleccionados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div v-if="expenses.links && expenses.links.length > 3" class="p-4 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <span class="text-xs text-zinc-500">
                    Página {{ expenses.current_page }} de {{ expenses.last_page }} ({{ expenses.total }} registros)
                </span>
                <div class="flex items-center gap-1">
                    <Link
                        v-for="(link, i) in expenses.links"
                        :key="i"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1 text-xs rounded-md border transition"
                        :class="[
                            link.active ? 'bg-amber-500 text-white border-amber-600 font-bold' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100',
                            !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
