<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
    Save,
    ArrowLeft,
    ClipboardList,
    Building2,
    Users,
    UserCheck,
    Calendar,
    FileText,
    Search,
    Info,
    Hash,
    AlertTriangle,
    CheckCircle2,
    Lock,
    Unlock,
} from '@lucide/vue';

interface Branch {
    id: number;
    name: string;
    code?: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    branches?: Branch[];
}

interface ClientBranch {
    id: number;
    name: string;
    code: string | null;
}

interface FolioItem {
    id: number;
    name: string;
    current_consecutive: number;
    description?: string | null;
}

interface Client {
    id: number;
    name: string;
    code: string;
    branches: ClientBranch[];
}

interface ExistingBitacoraFolio {
    folio_prefix: string;
    folio_consecutive: string;
    folio_number: string;
    client_id?: number;
    client_branch_id?: number | null;
    client?: {
        id: number;
        name: string;
        code: string;
    };
}

interface ExistingBitacoraRecord {
    folio_prefix: string;
    folio_consecutive: string;
    folio_number: string;
    date: string;
    client_id?: number;
    client_branch_id?: number | null;
    client?: {
        id: number;
        name: string;
        code: string;
    };
}

const props = defineProps<{
    branches: Branch[];
    users: User[];
    clients: Client[];
    folios?: FolioItem[];
    suggestedPrefix: string;
    suggestedConsecutive: string;
    existingBitacoraFolios?: ExistingBitacoraFolio[];
    existingBitacoras?: ExistingBitacoraRecord[];
    currentUserId?: number;
    defaultBranchId?: number;
}>();

const today = new Date().toISOString().substring(0, 10);
const clientSearch = ref('');
const clientBranchSearch = ref('');

const folioOptions = computed(() => {
    if (props.folios && props.folios.length > 0) {
        return props.folios;
    }
    return [
        { id: 1, name: 'BIT', current_consecutive: 0 },
        { id: 2, name: 'ICC', current_consecutive: 0 },
        { id: 3, name: 'SERV', current_consecutive: 0 },
        { id: 4, name: 'MANT', current_consecutive: 0 },
        { id: 5, name: 'OBRA', current_consecutive: 0 },
    ];
});

const initialUserId = props.currentUserId || (props.users.length > 0 ? props.users[0].id : '');
const initialUser = props.users.find(u => u.id === Number(initialUserId));
const initialBranchId = props.defaultBranchId || (initialUser?.branches && initialUser.branches.length > 0 ? initialUser.branches[0].id : (props.branches.length > 0 ? props.branches[0].id : ''));

const form = useForm({
    branch_id: initialBranchId,
    user_id: initialUserId,
    client_id: '' as number | string,
    client_branch_id: '' as number | string,
    folio_prefix: props.suggestedPrefix || 'BIT',
    folio_consecutive: props.suggestedConsecutive || '',
    date: today,
    notes: '',
});

// Branch in function of selected user
const selectedUser = computed(() => {
    return props.users.find(u => u.id === Number(form.user_id));
});

const availableBranches = computed(() => {
    if (selectedUser.value && selectedUser.value.branches && selectedUser.value.branches.length > 0) {
        return selectedUser.value.branches;
    }
    return props.branches;
});

// When user changes, update branch to one of the user's assigned branches if current branch is invalid
watch(() => form.user_id, (newUserId) => {
    if (!newUserId) return;
    const user = props.users.find(u => u.id === Number(newUserId));
    if (user && user.branches && user.branches.length > 0) {
        const hasCurrentBranch = user.branches.some(b => b.id === Number(form.branch_id));
        if (!hasCurrentBranch) {
            form.branch_id = user.branches[0].id;
        }
    }
});

// Check if current prefix + consecutive matches an existing folio
const matchedExistingFolio = computed(() => {
    if (!form.folio_prefix || !form.folio_consecutive) return null;
    const p = form.folio_prefix.trim().toUpperCase();
    const c = String(form.folio_consecutive).trim();
    const full = `${p}-${c}`;

    const fromFolios = (props.existingBitacoraFolios || []).find(
        f => f.folio_number?.toUpperCase() === full ||
            (f.folio_prefix?.toUpperCase() === p && String(f.folio_consecutive) === c)
    );
    if (fromFolios && fromFolios.client_id) return fromFolios;

    const fromBitacoras = (props.existingBitacoras || []).find(
        b => b.folio_number?.toUpperCase() === full ||
            (b.folio_prefix?.toUpperCase() === p && String(b.folio_consecutive) === c)
    );
    if (fromBitacoras && fromBitacoras.client_id) return fromBitacoras;

    return null;
});

const isClientLocked = computed(() => Boolean(matchedExistingFolio.value));

// When an existing folio is detected, lock and auto-set client
watch(matchedExistingFolio, (matched) => {
    if (matched && matched.client_id) {
        form.client_id = matched.client_id;
        form.client_branch_id = matched.client_branch_id || '';
    }
}, { immediate: true });

// Filtered Clients based on search input
const filteredClients = computed(() => {
    if (!clientSearch.value.trim()) {
        return props.clients;
    }
    const q = clientSearch.value.toLowerCase();
    return props.clients.filter(c =>
        c.name.toLowerCase().includes(q) || c.code.toLowerCase().includes(q)
    );
});

// Currently selected client object
const selectedClient = computed(() => {
    if (!form.client_id) return null;
    return props.clients.find(c => c.id === Number(form.client_id)) || null;
});

// Filtered client branches
const filteredClientBranches = computed(() => {
    if (!selectedClient.value || !selectedClient.value.branches) return [];
    if (!clientBranchSearch.value.trim()) {
        return selectedClient.value.branches;
    }
    const q = clientBranchSearch.value.toLowerCase();
    return selectedClient.value.branches.filter(b =>
        b.name.toLowerCase().includes(q) || (b.code && b.code.toLowerCase().includes(q))
    );
});

const onClientSelect = (clientId: number) => {
    if (isClientLocked.value) return;
    form.client_id = clientId;
    form.client_branch_id = '';
    clientBranchSearch.value = '';
};

const onFolioPrefixChange = () => {
    const selected = folioOptions.value.find(f => f.name === form.folio_prefix);
    if (selected) {
        form.folio_consecutive = String(selected.current_consecutive + 1);
    }
};

const existingFoliosForPrefix = computed(() => {
    if (!props.existingBitacoraFolios) return [];
    return props.existingBitacoraFolios.filter(
        f => f.folio_prefix.toUpperCase() === form.folio_prefix.toUpperCase()
    );
});

const selectExistingFolio = (consecutive: string) => {
    form.folio_consecutive = consecutive;
};

// Check if current folio prefix + consecutive already exists on the selected date
const isDuplicateOnSameDate = computed(() => {
    if (!form.folio_prefix || !form.folio_consecutive || !form.date) return false;
    const p = form.folio_prefix.trim().toUpperCase();
    const c = String(form.folio_consecutive).trim();
    const full = `${p}-${c}`;
    const d = form.date;
    return (props.existingBitacoras || []).some(
        b => b.folio_number.toUpperCase() === full && b.date.substring(0, 10) === d
    );
});

// Check if current folio prefix + consecutive exists on other dates
const previousDatesForFolio = computed(() => {
    if (!form.folio_prefix || !form.folio_consecutive) return [];
    const p = form.folio_prefix.trim().toUpperCase();
    const c = String(form.folio_consecutive).trim();
    const full = `${p}-${c}`;
    return (props.existingBitacoras || [])
        .filter(b => b.folio_number.toUpperCase() === full)
        .map(b => b.date.substring(0, 10));
});

// Date validation: no future dates allowed
const isFutureDate = computed(() => {
    return Boolean(form.date && form.date > today);
});

// Computed full folio number preview
const previewFolioNumber = computed(() => {
    const p = form.folio_prefix ? form.folio_prefix.trim() : '';
    const c = form.folio_consecutive ? String(form.folio_consecutive).trim() : '';
    return p && c ? `${p}-${c}` : (p || c || '---');
});

const submit = () => {
    if (isFutureDate.value || isDuplicateOnSameDate.value) {
        return;
    }
    form.post('/bitacoras');
};
</script>

<template>
    <Head title="Nueva Bitácora" />

    <div class="p-3 sm:p-6 lg:p-8 max-w-4xl mx-auto space-y-6 w-full min-w-0">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-4 sm:p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="flex items-center gap-3">
                <Link href="/bitacoras">
                    <Button variant="outline" size="icon" class="h-10 w-10 rounded-xl shrink-0">
                        <ArrowLeft class="h-5 w-5" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <ClipboardList class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600 dark:text-indigo-400" />
                        Crear Nueva Bitácora
                    </h1>
                    <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Genera el folio correspondiente y captura los datos generales de la bitácora.
                    </p>
                </div>
            </div>
            <div class="text-left sm:text-right bg-zinc-50 dark:bg-zinc-800/40 p-2.5 sm:p-0 rounded-xl sm:bg-transparent">
                <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider block">Vista previa de folio</span>
                <span class="text-base sm:text-lg font-mono font-bold text-indigo-600 dark:text-indigo-400">
                    {{ previewFolioNumber }}
                </span>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 p-4 rounded-xl flex items-start gap-3 text-indigo-900 dark:text-indigo-200 text-xs sm:text-sm">
            <Info class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400 mt-0.5" />
            <div>
                <span class="font-semibold block mb-0.5">Flujo de Creación de Bitácora</span>
                Primero define el folio y la fecha. Si reutilizas un folio de días previos, el cliente se fijará automáticamente para mantener la consistencia del servicio. La sucursal operativa se ajusta al usuario encargado seleccionado.
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <Card class="border-zinc-200 dark:border-zinc-800 shadow-sm">
                <CardHeader class="border-b border-zinc-100 dark:border-zinc-800/60 pb-4">
                    <CardTitle class="text-base sm:text-lg flex items-center gap-2">
                        <FileText class="h-5 w-5 text-indigo-600" />
                        Registro de Bitácora
                    </CardTitle>
                    <CardDescription>
                        Completa el folio, responsable, cliente y fecha de ejecución.
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-6 space-y-6">

                    <!-- ======================================================== -->
                    <!-- SECTION 1: FOLIO Y FECHA (FIRST SECTION AS REQUESTED) -->
                    <!-- ======================================================== -->
                    <div class="space-y-4 p-4 rounded-2xl bg-indigo-50/40 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="flex items-center justify-center h-6 w-6 rounded-lg bg-indigo-600 text-white font-bold text-xs">
                                    1
                                </span>
                                <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                    <Hash class="h-4 w-4 text-indigo-600" />
                                    Generación de Folio y Fecha
                                </h2>
                            </div>
                            <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-zinc-900 px-2.5 py-1 rounded-lg border border-indigo-200 dark:border-indigo-800">
                                Folio: {{ previewFolioNumber }}
                            </span>
                        </div>

                        <!-- Grid: Folio Prefix, Consecutive, Date -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <Label for="folio_prefix" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                    <Hash class="h-4 w-4 text-indigo-600" />
                                    Serie / Prefijo *
                                </Label>
                                <select
                                    id="folio_prefix"
                                    v-model="form.folio_prefix"
                                    @change="onFolioPrefixChange"
                                    required
                                    class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 text-sm focus:ring-2 focus:ring-indigo-500 font-mono font-bold"
                                >
                                    <option v-for="folio in folioOptions" :key="folio.id" :value="folio.name">
                                        {{ folio.name }} (Próximo: #{{ folio.current_consecutive + 1 }})
                                    </option>
                                </select>
                                <span v-if="form.errors.folio_prefix" class="text-xs text-red-500 font-medium">{{ form.errors.folio_prefix }}</span>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="folio_consecutive" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                    <Hash class="h-4 w-4 text-indigo-600" />
                                    Número Consecutivo *
                                </Label>
                                <Input
                                    id="folio_consecutive"
                                    type="number"
                                    min="1"
                                    v-model="form.folio_consecutive"
                                    placeholder="Ej: 1"
                                    required
                                    class="font-mono h-10 rounded-xl text-sm"
                                />
                                <span v-if="form.errors.folio_consecutive" class="text-xs text-red-500 font-medium">{{ form.errors.folio_consecutive }}</span>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="date" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                    <Calendar class="h-4 w-4 text-indigo-600" />
                                    Fecha de la Bitácora *
                                </Label>
                                <Input
                                    id="date"
                                    type="date"
                                    v-model="form.date"
                                    :max="today"
                                    required
                                    class="h-10 rounded-xl text-sm"
                                    :class="isFutureDate ? 'border-red-500 focus:ring-red-500' : ''"
                                />
                                <span v-if="form.errors.date" class="text-xs text-red-500 font-medium">{{ form.errors.date }}</span>
                                <span v-if="isFutureDate" class="text-xs text-red-600 dark:text-red-400 font-medium block">
                                    No se permiten fechas posteriores a hoy (máx: {{ today }}).
                                </span>
                            </div>
                        </div>

                        <!-- Duplicate Folio on Same Date Alert -->
                        <div
                            v-if="isDuplicateOnSameDate"
                            class="p-3 bg-red-50 dark:bg-red-950/40 rounded-xl border border-red-200 dark:border-red-800 text-xs text-red-800 dark:text-red-200 space-y-1"
                        >
                            <div class="font-bold flex items-center gap-1.5 text-red-700 dark:text-red-300">
                                <AlertTriangle class="h-4 w-4 shrink-0 text-red-600" />
                                Folio ya registrado en esta misma fecha
                            </div>
                            <p>
                                La bitácora con folio <strong>{{ previewFolioNumber }}</strong> ya existe para el día <strong>{{ form.date }}</strong>. Para reutilizarlo debes cambiar la fecha o asignar otro número consecutivo.
                            </p>
                        </div>

                        <!-- Reusing Folio Alert -->
                        <div
                            v-else-if="previousDatesForFolio.length > 0"
                            class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-200 space-y-1"
                        >
                            <div class="font-bold flex items-center gap-1.5 text-emerald-700 dark:text-emerald-300">
                                <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-600" />
                                Reutilizando folio existente para nueva fecha
                            </div>
                            <p>
                                Folio activo con registros previos en: <strong>{{ previousDatesForFolio.join(', ') }}</strong>. Al registrarlo para el <strong>{{ form.date }}</strong>, los reportes sumarán todos los gastos y nóminas de este folio automáticamente.
                            </p>
                        </div>

                        <!-- Existing Folio Quick Selector Pills -->
                        <div v-if="existingFoliosForPrefix.length > 0" class="pt-2 border-t border-indigo-100 dark:border-indigo-900/40 flex items-center gap-1.5 flex-wrap">
                            <span class="text-[11px] font-semibold text-zinc-500 uppercase">Folios existentes en {{ form.folio_prefix }}:</span>
                            <button
                                v-for="ef in existingFoliosForPrefix"
                                :key="ef.folio_number"
                                type="button"
                                @click="selectExistingFolio(ef.folio_consecutive)"
                                class="px-2.5 py-1 rounded-lg font-mono text-xs transition border flex items-center gap-1.5"
                                :class="String(form.folio_consecutive) === String(ef.folio_consecutive)
                                    ? 'bg-indigo-600 text-white border-indigo-600 font-bold shadow-sm'
                                    : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100'"
                            >
                                <Lock v-if="ef.client_id" class="h-3 w-3" />
                                <span>{{ ef.folio_number }}</span>
                                <span v-if="ef.client" class="text-[10px] opacity-75 font-sans">({{ ef.client.code }})</span>
                            </button>
                        </div>
                    </div>

                    <!-- ======================================================== -->
                    <!-- SECTION 2: USUARIO RESPONSABLE & SUCURSAL OPERATIVA -->
                    <!-- ======================================================== -->
                    <div class="space-y-4 pt-2">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center h-6 w-6 rounded-lg bg-indigo-600 text-white font-bold text-xs">
                                2
                            </span>
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                <UserCheck class="h-4 w-4 text-indigo-600" />
                                Usuario Encargado y Sucursal Operativa
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <!-- Usuario Encargado -->
                            <div class="space-y-1.5">
                                <Label for="user_id" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                    <UserCheck class="h-4 w-4 text-indigo-600" />
                                    Usuario Encargado / Responsable *
                                </Label>
                                <select
                                    id="user_id"
                                    v-model="form.user_id"
                                    required
                                    class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 text-sm focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="" disabled>Selecciona al usuario responsable</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">
                                        {{ u.name }} ({{ u.email }})
                                    </option>
                                </select>
                                <span v-if="form.errors.user_id" class="text-xs text-red-500 font-medium">{{ form.errors.user_id }}</span>
                            </div>

                            <!-- Sucursal Operativa (ICC) - en función del usuario -->
                            <div class="space-y-1.5">
                                <Label for="branch_id" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <Building2 class="h-4 w-4 text-indigo-600" />
                                        Sucursal Operativa (ICC) *
                                    </span>
                                    <span v-if="selectedUser?.branches?.length" class="text-[10px] text-indigo-600 dark:text-indigo-400 font-normal">
                                        Filtrada por usuario
                                    </span>
                                </Label>
                                <select
                                    id="branch_id"
                                    v-model="form.branch_id"
                                    required
                                    class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 text-sm focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="" disabled>Selecciona una sucursal</option>
                                    <option v-for="b in availableBranches" :key="b.id" :value="b.id">
                                        {{ b.name }} {{ b.code ? `(${b.code})` : '' }}
                                    </option>
                                </select>
                                <span v-if="form.errors.branch_id" class="text-xs text-red-500 font-medium">{{ form.errors.branch_id }}</span>
                                <span v-if="selectedUser?.branches?.length" class="text-[11px] text-zinc-500 block">
                                    Sucursal(es) del usuario: {{ selectedUser.branches.map(b => b.name).join(', ') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ======================================================== -->
                    <!-- SECTION 3: CLIENTE & SUCURSAL DEL CLIENTE (LOCKED ON EXISTING FOLIO) -->
                    <!-- ======================================================== -->
                    <div class="space-y-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="flex items-center justify-center h-6 w-6 rounded-lg bg-indigo-600 text-white font-bold text-xs">
                                    3
                                </span>
                                <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                    <Users class="h-4 w-4 text-indigo-600" />
                                    Cliente y Sucursal de Atención
                                </h2>
                            </div>

                            <Badge
                                v-if="isClientLocked"
                                class="bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-200 border-amber-300 dark:border-amber-800 flex items-center gap-1 text-xs py-1 px-2.5"
                            >
                                <Lock class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                Cliente fijo por folio {{ previewFolioNumber }}
                            </Badge>
                        </div>

                        <!-- Locked Client Notice -->
                        <div
                            v-if="isClientLocked"
                            class="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-200 flex items-start gap-2.5"
                        >
                            <Lock class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" />
                            <div>
                                <span class="font-bold block">Cliente determinado por folio previo</span>
                                El folio <strong>{{ previewFolioNumber }}</strong> ya tiene historial con el cliente <strong>{{ selectedClient?.name || 'Cliente asignado' }}</strong>. Para mantener la consistencia histórica del folio, no se permite cambiar de cliente.
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <!-- Cliente Selector -->
                            <div class="space-y-1.5">
                                <Label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <Users class="h-4 w-4 text-indigo-600" />
                                        Cliente / Razón Social *
                                    </span>
                                    <span v-if="isClientLocked" class="text-[10px] text-amber-600 font-semibold flex items-center gap-1">
                                        <Lock class="h-3 w-3" /> Bloqueado
                                    </span>
                                </Label>

                                <div class="space-y-2" :class="isClientLocked ? 'opacity-85' : ''">
                                    <div v-if="!isClientLocked" class="relative">
                                        <Search class="h-4 w-4 absolute left-3 top-3 text-zinc-400" />
                                        <Input
                                            v-model="clientSearch"
                                            placeholder="Filtrar clientes por nombre o código..."
                                            class="pl-9 text-xs h-9 rounded-lg"
                                        />
                                    </div>

                                    <div v-if="isClientLocked" class="p-3 bg-zinc-100 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                                        <div>
                                            <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100 block">
                                                [{{ selectedClient?.code }}] {{ selectedClient?.name }}
                                            </span>
                                            <span class="text-[11px] text-zinc-500">Asignado por el folio {{ previewFolioNumber }}</span>
                                        </div>
                                        <Lock class="h-4 w-4 text-zinc-400" />
                                    </div>

                                    <select
                                        v-else
                                        v-model="form.client_id"
                                        @change="onClientSelect(Number(form.client_id))"
                                        required
                                        size="4"
                                        class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2 text-xs focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option v-for="c in filteredClients" :key="c.id" :value="c.id" class="p-1.5 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
                                            [{{ c.code }}] {{ c.name }}
                                        </option>
                                        <option v-if="filteredClients.length === 0" disabled class="p-2 text-zinc-400 italic">
                                            No se encontraron clientes coincidentes
                                        </option>
                                    </select>
                                </div>
                                <span v-if="form.errors.client_id" class="text-xs text-red-500 font-medium">{{ form.errors.client_id }}</span>
                            </div>

                            <!-- Sucursal de Cliente Selector -->
                            <div class="space-y-1.5">
                                <Label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <Building2 class="h-4 w-4 text-indigo-600" />
                                        Sucursal del Cliente
                                    </span>
                                    <span v-if="isClientLocked" class="text-[10px] text-amber-600 font-semibold flex items-center gap-1">
                                        <Lock class="h-3 w-3" /> Bloqueado
                                    </span>
                                </Label>

                                <div class="space-y-2" :class="isClientLocked ? 'opacity-85' : (!selectedClient ? 'opacity-50 pointer-events-none' : '')">
                                    <div v-if="isClientLocked" class="p-3 bg-zinc-100 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                                        <div>
                                            <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100 block">
                                                {{ (selectedClient?.branches?.find(b => b.id === Number(form.client_branch_id))?.name) || '• No Aplica (Cliente General / Matriz Única)' }}
                                            </span>
                                            <span class="text-[11px] text-zinc-500">Determinada por el folio previo {{ previewFolioNumber }}</span>
                                        </div>
                                        <Lock class="h-4 w-4 text-zinc-400" />
                                    </div>

                                    <template v-else>
                                        <div class="relative">
                                            <Search class="h-4 w-4 absolute left-3 top-3 text-zinc-400" />
                                            <Input
                                                v-model="clientBranchSearch"
                                                placeholder="Filtrar sucursales del cliente..."
                                                class="pl-9 text-xs h-9 rounded-lg"
                                                :disabled="!selectedClient"
                                            />
                                        </div>

                                        <select
                                            v-model="form.client_branch_id"
                                            size="4"
                                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2 text-xs focus:ring-2 focus:ring-indigo-500"
                                            :disabled="!selectedClient"
                                        >
                                            <option value="" class="p-1.5 rounded text-indigo-600 font-semibold cursor-pointer">
                                                • No Aplica (Cliente General / Matriz Única)
                                            </option>
                                            <option
                                                v-for="cb in filteredClientBranches"
                                                :key="cb.id"
                                                :value="cb.id"
                                                class="p-1.5 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                                            >
                                                {{ cb.name }} {{ cb.code ? `(${cb.code})` : '' }}
                                            </option>
                                            <option v-if="selectedClient && filteredClientBranches.length === 0" disabled class="p-2 text-zinc-400 italic">
                                                No hay sucursales registradas para este cliente
                                            </option>
                                        </select>
                                    </template>
                                </div>
                                <span v-if="form.errors.client_branch_id" class="text-xs text-red-500 font-medium">{{ form.errors.client_branch_id }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ======================================================== -->
                    <!-- SECTION 4: NOTAS U OBSERVACIONES -->
                    <!-- ======================================================== -->
                    <div class="space-y-1.5 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <Label for="notes" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                            Notas u Observaciones Generales (Opcional)
                        </Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            placeholder="Añade cualquier instrucción inicial, orden de compra o detalle relevante..."
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-3 text-sm focus:ring-2 focus:ring-indigo-500"
                        ></textarea>
                        <span v-if="form.errors.notes" class="text-xs text-red-500 font-medium">{{ form.errors.notes }}</span>
                    </div>
                </CardContent>
            </Card>

            <!-- Bottom Actions -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-2">
                <Link href="/bitacoras" class="w-full sm:w-auto">
                    <Button type="button" variant="outline" class="w-full sm:w-auto rounded-xl">
                        Cancelar
                    </Button>
                </Link>
                <Button
                    type="submit"
                    :disabled="form.processing || isDuplicateOnSameDate || !form.folio_consecutive || isFutureDate"
                    class="w-full sm:w-auto rounded-xl shadow-md px-6 flex items-center justify-center gap-2 transition"
                    :class="isDuplicateOnSameDate || isFutureDate ? 'bg-red-600/80 hover:bg-red-600/80 text-white cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 text-white'"
                >
                    <AlertTriangle v-if="isDuplicateOnSameDate || isFutureDate" class="h-4 w-4" />
                    <Save v-else class="h-4 w-4" />
                    {{ form.processing ? 'Creando Bitácora...' : (isFutureDate ? 'Fecha Futura No Permitida' : (isDuplicateOnSameDate ? 'Folio Duplicado en esta Fecha' : 'Guardar y Continuar a Actividades')) }}
                </Button>
            </div>
        </form>
    </div>
</template>

