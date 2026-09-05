<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    Hash
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
}

const props = defineProps<{
    branches: Branch[];
    users: User[];
    clients: Client[];
    folios?: FolioItem[];
    suggestedPrefix: string;
    suggestedConsecutive: string;
    existingBitacoraFolios?: ExistingBitacoraFolio[];
}>();

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

const form = useForm({
    branch_id: props.branches.length > 0 ? props.branches[0].id : '',
    user_id: props.users.length > 0 ? props.users[0].id : '',
    client_id: '',
    client_branch_id: '',
    folio_prefix: props.suggestedPrefix || 'BIT',
    folio_consecutive: props.suggestedConsecutive || '',
    date: new Date().toISOString().substring(0, 10),
    notes: '',
});

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

// Computed full folio number preview
const previewFolioNumber = computed(() => {
    const p = form.folio_prefix ? form.folio_prefix.trim() : '';
    const c = form.folio_consecutive ? String(form.folio_consecutive).trim() : '';
    return p && c ? `${p}-${c}` : (p || c || '---');
});

const submit = () => {
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
                        Alta de bitácora general asignada a un cliente, sucursal y usuario encargado.
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
                <span class="font-semibold block mb-0.5">Flujo de Bitácora Operativa</span>
                Al guardar los datos generales, la bitácora quedará registrada y los usuarios encargados asignados podrán capturar las actividades, personal y gastos correspondientes desde la edición.
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Main Data Card -->
            <Card class="border-zinc-200 dark:border-zinc-800 shadow-sm">
                <CardHeader class="border-b border-zinc-100 dark:border-zinc-800/60 pb-4">
                    <CardTitle class="text-base sm:text-lg flex items-center gap-2">
                        <FileText class="h-5 w-5 text-indigo-600" />
                        Datos Generales y Asignación
                    </CardTitle>
                    <CardDescription>
                        Selecciona el cliente, la sucursal de atención y el usuario responsable.
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-6 space-y-6">
                    <!-- Row 1: Sucursal ICC & Usuario Asignado -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="space-y-1.5">
                            <Label for="branch_id" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                <Building2 class="h-4 w-4 text-indigo-600" />
                                Sucursal Operativa (ICC) *
                            </Label>
                            <select
                                id="branch_id"
                                v-model="form.branch_id"
                                required
                                class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 text-sm focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="" disabled>Selecciona una sucursal</option>
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                            <span v-if="form.errors.branch_id" class="text-xs text-red-500 font-medium">{{ form.errors.branch_id }}</span>
                        </div>

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
                    </div>

                    <!-- Row 2: Cliente con Buscador y Sucursal de Cliente -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <!-- Cliente Searchable Box -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                <Users class="h-4 w-4 text-indigo-600" />
                                Cliente / Razón Social *
                            </Label>

                            <div class="space-y-2">
                                <div class="relative">
                                    <Search class="h-4 w-4 absolute left-3 top-3 text-zinc-400" />
                                    <Input
                                        v-model="clientSearch"
                                        placeholder="Filtrar clientes por nombre o código..."
                                        class="pl-9 text-xs h-9 rounded-lg"
                                    />
                                </div>

                                <select
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

                        <!-- Sucursal de Cliente Searchable Box -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                <Building2 class="h-4 w-4 text-indigo-600" />
                                Sucursal del Cliente
                            </Label>

                            <div class="space-y-2" :class="!selectedClient ? 'opacity-50 pointer-events-none' : ''">
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
                            </div>
                            <span v-if="form.errors.client_branch_id" class="text-xs text-red-500 font-medium">{{ form.errors.client_branch_id }}</span>
                        </div>
                    </div>

                    <!-- Row 3: Folio Prefix & Consecutive & Date -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="space-y-1.5">
                            <Label for="folio_prefix" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                <Hash class="h-4 w-4 text-indigo-600" />
                                Serie / Prefijo de Folio *
                            </Label>
                            <div class="flex gap-2">
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
                            </div>
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
                                required
                                class="h-10 rounded-xl text-sm"
                            />
                            <span v-if="form.errors.date" class="text-xs text-red-500 font-medium">{{ form.errors.date }}</span>
                        </div>
                    </div>

                    <!-- Existing Folio Quick Pills & Multi-date Information -->
                    <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs">
                        <div class="flex items-start gap-2 text-zinc-600 dark:text-zinc-400">
                            <Info class="h-4 w-4 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5" />
                            <div>
                                <span class="font-semibold text-zinc-900 dark:text-zinc-200">Reutilización de folios para múltiples días:</span>
                                Puedes usar la misma serie y folio siempre que la fecha sea diferente (por ejemplo, para continuar un servicio en varios días). Los reportes sumarán los gastos y salarios de los folios idénticos automáticamente.
                            </div>
                        </div>

                        <div v-if="existingFoliosForPrefix.length > 0" class="pt-1.5 border-t border-zinc-200/60 dark:border-zinc-700/60 flex items-center gap-1.5 flex-wrap">
                            <span class="text-[11px] font-semibold text-zinc-500 uppercase">Folios existentes en {{ form.folio_prefix }}:</span>
                            <button
                                v-for="ef in existingFoliosForPrefix"
                                :key="ef.folio_number"
                                type="button"
                                @click="selectExistingFolio(ef.folio_consecutive)"
                                class="px-2 py-0.5 rounded-md font-mono text-xs transition border"
                                :class="String(form.folio_consecutive) === String(ef.folio_consecutive)
                                    ? 'bg-indigo-600 text-white border-indigo-600 font-bold'
                                    : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100'"
                            >
                                {{ ef.folio_number }}
                            </button>
                        </div>
                    </div>

                    <!-- Row 4: Notas generales -->
                    <div class="space-y-1.5 pt-2 border-t border-zinc-100 dark:border-zinc-800">
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
                    :disabled="form.processing"
                    class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md px-6 flex items-center justify-center gap-2"
                >
                    <Save class="h-4 w-4" />
                    {{ form.processing ? 'Creando Bitácora...' : 'Guardar y Continuar a Actividades' }}
                </Button>
            </div>
        </form>
    </div>
</template>
