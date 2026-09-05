<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ActionsDropdown from '@/components/ActionsDropdown.vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
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
    FileText
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

interface Bitacora {
    id: number;
    folio_number: string;
    date: string;
    notes: string | null;
    branch?: Branch;
    user?: { name: string };
    client?: Client;
    client_branch?: { name: string };
    clientBranch?: { name: string };
    activities?: {
        id: number;
        date: string;
        description: string;
        employees?: { total_earned: number }[];
        expenses?: { amount: number }[];
    }[];
}

const props = defineProps<{
    bitacoras: {
        data: Bitacora[];
        links: any[];
    };
    branches: Branch[];
    clients: Client[];
    filters: {
        search?: string;
        branch_id?: string;
        client_id?: string;
        start_date?: string;
        end_date?: string;
    };
    canCreate: boolean;
}>();

const search = ref(props.filters.search || '');
const branchId = ref(props.filters.branch_id || '');
const clientId = ref(props.filters.client_id || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const showDeleteDialog = ref(false);
const selectedBitacora = ref<Bitacora | null>(null);

const handleSearch = () => {
    router.get('/bitacoras', {
        search: search.value,
        branch_id: branchId.value,
        client_id: clientId.value,
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true, replace: true });
};

const openDeleteConfirm = (bitacora: Bitacora) => {
    selectedBitacora.value = bitacora;
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

const isSundayDate = (dateStr?: string): boolean => {
    if (!dateStr) return false;
    const d = new Date(dateStr + 'T00:00:00');
    return d.getDay() === 0;
};

// Check if any activity of the bitacora or the bitacora date itself is on a Sunday
const hasSundayActivity = (b: Bitacora): boolean => {
    if (isSundayDate(b.date)) return true;
    return (b.activities || []).some(a => isSundayDate(a.date));
};

const calculateBitacoraExpenses = (b: Bitacora) => {
    return (b.activities || []).reduce((sum, act) => {
        return sum + (act.expenses || []).reduce((s, e) => s + (Number(e.amount) || 0), 0);
    }, 0);
};

const calculateBitacoraPayroll = (b: Bitacora) => {
    return (b.activities || []).reduce((sum, act) => {
        return sum + (act.employees || []).reduce((s, e) => s + (Number(e.total_earned) || 0), 0);
    }, 0);
};

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(val);
};

const folioCounts = computed(() => {
    const counts: Record<string, number> = {};
    (props.bitacoras.data || []).forEach(b => {
        if (b.folio_number) {
            counts[b.folio_number] = (counts[b.folio_number] || 0) + 1;
        }
    });
    return counts;
});
</script>

<template>
    <Head title="Bitácoras" />

    <div class="p-3 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full min-w-0">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-4 sm:p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <ClipboardList class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Bitácoras Operativas
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Control de folios, clientes, actividades de mantenimiento, personal y gastos operativos.
                </p>
            </div>
            <div v-if="canCreate">
                <Link href="/bitacoras/create">
                    <Button class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow w-full sm:w-auto">
                        <Plus class="h-4 w-4 mr-2" /> Nueva Bitácora
                    </Button>
                </Link>
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

        <!-- Bitacoras Container -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm w-full min-w-0">
            <!-- Mobile View: Card List (md:hidden) -->
            <div class="block md:hidden divide-y divide-zinc-200 dark:divide-zinc-800">
                <div
                    v-for="b in bitacoras.data"
                    :key="b.id"
                    class="p-4 space-y-3 transition"
                    :class="hasSundayActivity(b)
                        ? 'bg-amber-50/60 dark:bg-amber-950/20'
                        : 'hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40'"
                >
                    <!-- Top Row: Folio + Date & Sunday badge + Actions -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <Link :href="`/bitacoras/${b.id}`" class="text-base font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ b.folio_number }}
                                </Link>
                                <Badge v-if="folioCounts[b.folio_number] > 1" class="bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 text-[10px] py-0 px-1 font-semibold">
                                    🗓️ Multi-fecha ({{ folioCounts[b.folio_number] }} días)
                                </Badge>
                            </div>
                            <div class="flex items-center gap-1.5 font-mono text-xs text-zinc-500 mt-0.5">
                                <Calendar class="h-3.5 w-3.5 text-zinc-400" />
                                <span>{{ b.date }}</span>
                                <Badge v-if="hasSundayActivity(b)" class="bg-amber-500 text-white text-[10px] py-0 px-1.5 gap-0.5">
                                    <Sun class="h-3 w-3" /> Domingo
                                </Badge>
                            </div>
                        </div>
                        <ActionsDropdown
                            :actions="[
                                { label: 'Ver Detalle', icon: 'view', onClick: () => router.get(`/bitacoras/${b.id}`) },
                                { label: 'Editar Actividades', icon: 'edit', onClick: () => router.get(`/bitacoras/${b.id}/edit`) },
                                { label: 'Eliminar', icon: 'delete', variant: 'destructive', onClick: () => openDeleteConfirm(b) }
                            ]"
                        />
                    </div>

                    <!-- Client & Branch Details -->
                    <div class="text-xs space-y-1 bg-zinc-50 dark:bg-zinc-800/50 p-2.5 rounded-xl border border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Cliente:</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100 text-right">{{ b.client?.name || 'Cliente General' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Sucursal Cliente:</span>
                            <span class="text-zinc-700 dark:text-zinc-300 text-right">{{ b.client_branch?.name || b.clientBranch?.name || 'Matriz / General' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Sucursal ICC:</span>
                            <span class="text-zinc-700 dark:text-zinc-300 text-right">{{ b.branch?.name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 font-medium">Encargado:</span>
                            <span class="text-zinc-700 dark:text-zinc-300 text-right">{{ b.user?.name || 'Sistema' }}</span>
                        </div>
                    </div>

                    <!-- Totals Row -->
                    <div class="grid grid-cols-3 gap-2 pt-1 text-center text-xs">
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <span class="text-[10px] text-zinc-400 uppercase font-semibold block">Actividades</span>
                            <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ b.activities?.length || 0 }}</span>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <span class="text-[10px] text-zinc-400 uppercase font-semibold block">Nómina</span>
                            <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100">{{ formatCurrency(calculateBitacoraPayroll(b)) }}</span>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800/40 p-2 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <span class="text-[10px] text-zinc-400 uppercase font-semibold block">Gastos</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(calculateBitacoraExpenses(b)) }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="bitacoras.data.length === 0" class="py-12 text-center text-zinc-400 italic text-xs">
                    No se encontraron bitácoras para los filtros seleccionados.
                </div>
            </div>

            <!-- Desktop View: Table (hidden on mobile, visible on md+) -->
            <div class="hidden md:block overflow-x-auto w-full">
                <table class="w-full text-left text-xs text-zinc-600 dark:text-zinc-300 min-w-[750px]">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-4">Folio</th>
                            <th class="py-3 px-4">Fecha</th>
                            <th class="py-3 px-4">Cliente / Sucursal</th>
                            <th class="py-3 px-4">Sucursal ICC</th>
                            <th class="py-3 px-4">Encargado</th>
                            <th class="py-3 px-4 text-center">Actividades</th>
                            <th class="py-3 px-4 text-right">Nómina</th>
                            <th class="py-3 px-4 text-right">Gastos</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <tr
                            v-for="b in bitacoras.data"
                            :key="b.id"
                            class="transition"
                            :class="hasSundayActivity(b)
                                ? 'bg-amber-50/60 dark:bg-amber-950/20 hover:bg-amber-100/60 dark:hover:bg-amber-900/30'
                                : 'hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40'"
                        >
                            <!-- Folio Number -->
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <Link :href="`/bitacoras/${b.id}`" class="hover:underline">
                                        {{ b.folio_number }}
                                    </Link>
                                    <Badge v-if="folioCounts[b.folio_number] > 1" class="bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 text-[10px] py-0 px-1 font-semibold">
                                        {{ folioCounts[b.folio_number] }} fechas
                                    </Badge>
                                </div>
                            </td>

                            <!-- Date + Sunday Indicator -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 font-mono">
                                    <Calendar class="h-3.5 w-3.5 text-zinc-400" />
                                    <span>{{ b.date }}</span>
                                    <Badge v-if="hasSundayActivity(b)" class="bg-amber-500 hover:bg-amber-600 text-white text-[10px] py-0 px-1.5 gap-0.5 shadow-xs">
                                        <Sun class="h-3 w-3" /> Domingo
                                    </Badge>
                                </div>
                            </td>

                            <!-- Client -->
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100 block">
                                    {{ b.client?.name || 'Cliente General' }}
                                </span>
                                <span class="text-[11px] text-zinc-400 block">
                                    {{ b.client_branch?.name || b.clientBranch?.name || 'Matriz / General' }}
                                </span>
                            </td>

                            <!-- Branch ICC -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ b.branch?.name }}
                                </span>
                            </td>

                            <!-- User Assigned -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-zinc-600 dark:text-zinc-400">
                                {{ b.user?.name || 'Sistema' }}
                            </td>

                            <!-- Activities Count -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <Badge variant="secondary" class="font-mono text-xs">
                                    {{ b.activities?.length || 0 }} act.
                                </Badge>
                            </td>

                            <!-- Payroll -->
                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                {{ formatCurrency(calculateBitacoraPayroll(b)) }}
                            </td>

                            <!-- Expenses -->
                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                {{ formatCurrency(calculateBitacoraExpenses(b)) }}
                            </td>

                            <!-- Actions Dropdown -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <ActionsDropdown
                                    :actions="[
                                        { label: 'Ver Detalle', icon: 'view', onClick: () => router.get(`/bitacoras/${b.id}`) },
                                        { label: 'Editar Actividades', icon: 'edit', onClick: () => router.get(`/bitacoras/${b.id}/edit`) },
                                        { label: 'Eliminar', icon: 'delete', variant: 'destructive', onClick: () => openDeleteConfirm(b) }
                                    ]"
                                />
                            </td>
                        </tr>
                        <tr v-if="bitacoras.data.length === 0">
                            <td colspan="9" class="py-12 text-center text-zinc-400 italic">
                                No se encontraron bitácoras para los filtros seleccionados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
        :open="showDeleteDialog"
        @update:open="showDeleteDialog = $event"
        title="¿Eliminar Bitácora?"
        :description="`¿Estás seguro de enviar la bitácora Folio '${selectedBitacora?.folio_number}' a la papelera (SoftDelete)?`"
        @confirm="confirmDelete"
    />
</template>
