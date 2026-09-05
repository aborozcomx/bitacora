<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DataTable, { ColumnDef } from '@/components/DataTable.vue';
import ActionsDropdown from '@/components/ActionsDropdown.vue';
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter
} from '@/components/ui/dialog';
import {
    Plus,
    Users,
    Building2,
    Phone,
    Mail,
    MapPin,
    ChevronDown,
    ChevronUp,
    Trash2,
    Edit
} from '@lucide/vue';

interface ClientBranch {
    id: number;
    client_id: number;
    name: string;
    code: string | null;
    address: string | null;
    contact_name: string | null;
    phone: string | null;
    email: string | null;
    is_active: boolean;
}

interface Client {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
    branches: ClientBranch[];
}

const props = defineProps<{
    clients: {
        data: Client[];
    };
}>();

const columns: ColumnDef[] = [
    { key: 'expander', label: '', align: 'center' },
    { key: 'code', label: 'Código', sortable: true },
    { key: 'name', label: 'Razón Social / Nombre', sortable: true },
    { key: 'branches_count', label: 'Sucursales', sortable: true, align: 'center' },
    { key: 'is_active', label: 'Estado', sortable: true },
    { key: 'actions', label: 'Acciones', align: 'right' },
];

const expandedClientIds = ref<number[]>([]);

const toggleClientExpand = (clientId: number) => {
    if (expandedClientIds.value.includes(clientId)) {
        expandedClientIds.value = expandedClientIds.value.filter(id => id !== clientId);
    } else {
        expandedClientIds.value.push(clientId);
    }
};

// Client Modal State
const showClientModal = ref(false);
const showDeleteClientDialog = ref(false);
const selectedClient = ref<Client | null>(null);
const isEditingClient = ref(false);

const clientForm = useForm({
    name: '',
    code: '',
    is_active: true,
});

const openCreateClientModal = () => {
    isEditingClient.value = false;
    selectedClient.value = null;
    clientForm.reset();
    clientForm.clearErrors();
    showClientModal.value = true;
};

const openEditClientModal = (client: Client) => {
    isEditingClient.value = true;
    selectedClient.value = client;
    clientForm.clearErrors();
    clientForm.name = client.name;
    clientForm.code = client.code;
    clientForm.is_active = client.is_active;
    showClientModal.value = true;
};

const openDeleteClientConfirm = (client: Client) => {
    selectedClient.value = client;
    showDeleteClientDialog.value = true;
};

const submitClientForm = () => {
    if (isEditingClient.value && selectedClient.value) {
        clientForm.put(`/catalogs/clients/${selectedClient.value.id}`, {
            onSuccess: () => {
                showClientModal.value = false;
                clientForm.reset();
            },
        });
    } else {
        clientForm.post('/catalogs/clients', {
            onSuccess: () => {
                showClientModal.value = false;
                clientForm.reset();
            },
        });
    }
};

const confirmDeleteClient = () => {
    if (selectedClient.value) {
        router.delete(`/catalogs/clients/${selectedClient.value.id}`, {
            onSuccess: () => {
                showDeleteClientDialog.value = false;
                selectedClient.value = null;
            },
        });
    }
};

// Branch Modal State
const showBranchModal = ref(false);
const showDeleteBranchDialog = ref(false);
const selectedBranch = ref<ClientBranch | null>(null);
const parentClientForBranch = ref<Client | null>(null);
const isEditingBranch = ref(false);

const branchForm = useForm({
    name: '',
    code: '',
    address: '',
    contact_name: '',
    phone: '',
    email: '',
    is_active: true,
});

const openCreateBranchModal = (client: Client) => {
    isEditingBranch.value = false;
    parentClientForBranch.value = client;
    selectedBranch.value = null;
    branchForm.reset();
    branchForm.clearErrors();
    showBranchModal.value = true;
};

const openEditBranchModal = (client: Client, branch: ClientBranch) => {
    isEditingBranch.value = true;
    parentClientForBranch.value = client;
    selectedBranch.value = branch;
    branchForm.clearErrors();
    branchForm.name = branch.name;
    branchForm.code = branch.code || '';
    branchForm.address = branch.address || '';
    branchForm.contact_name = branch.contact_name || '';
    branchForm.phone = branch.phone || '';
    branchForm.email = branch.email || '';
    branchForm.is_active = branch.is_active;
    showBranchModal.value = true;
};

const openDeleteBranchConfirm = (branch: ClientBranch) => {
    selectedBranch.value = branch;
    showDeleteBranchDialog.value = true;
};

const submitBranchForm = () => {
    if (isEditingBranch.value && selectedBranch.value) {
        branchForm.put(`/catalogs/clients/branches/${selectedBranch.value.id}`, {
            onSuccess: () => {
                showBranchModal.value = false;
                branchForm.reset();
            },
        });
    } else if (parentClientForBranch.value) {
        branchForm.post(`/catalogs/clients/${parentClientForBranch.value.id}/branches`, {
            onSuccess: () => {
                showBranchModal.value = false;
                branchForm.reset();
            },
        });
    }
};

const confirmDeleteBranch = () => {
    if (selectedBranch.value) {
        router.delete(`/catalogs/clients/branches/${selectedBranch.value.id}`, {
            onSuccess: () => {
                showDeleteBranchDialog.value = false;
                selectedBranch.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Clientes y Sucursales" />

    <div class="p-6 space-y-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <Users class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Catálogo de Clientes y Sucursales
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Administra los clientes y sus respectivas sucursales para la creación y asignación en bitácoras.
                </p>
            </div>
            <Button @click="openCreateClientModal" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow">
                <Plus class="h-4 w-4 mr-2" /> Nuevo Cliente
            </Button>
        </div>

        <!-- DataTable Component -->
        <DataTable
            :columns="columns"
            :data="clients.data"
            searchPlaceholder="Buscar cliente o sucursal por código, nombre, contacto..."
        >
            <template #cell-expander="{ row }">
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200"
                    @click="toggleClientExpand(row.id)"
                    :title="expandedClientIds.includes(row.id) ? 'Ocultar sucursales' : 'Ver sucursales'"
                >
                    <ChevronUp v-if="expandedClientIds.includes(row.id)" class="h-4 w-4" />
                    <ChevronDown v-else class="h-4 w-4" />
                </Button>
            </template>

            <template #cell-code="{ row }">
                <span class="font-mono font-medium text-indigo-600 dark:text-indigo-400">{{ row.code }}</span>
            </template>

            <template #cell-name="{ row }">
                <div>
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100 block">{{ row.name }}</span>
                </div>
            </template>

            <template #cell-branches_count="{ row }">
                <Badge variant="outline" class="font-mono text-xs">
                    {{ row.branches?.length || 0 }} {{ (row.branches?.length === 1) ? 'sucursal' : 'sucursales' }}
                </Badge>
            </template>

            <template #cell-is_active="{ row }">
                <Badge :variant="row.is_active ? 'default' : 'secondary'" :class="row.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border-emerald-200' : ''">
                    {{ row.is_active ? 'Activo' : 'Inactivo' }}
                </Badge>
            </template>

            <template #cell-actions="{ row }">
                <ActionsDropdown
                    :actions="[
                        { label: 'Nueva Sucursal', icon: 'plus', onClick: () => openCreateBranchModal(row) },
                        { label: 'Editar Cliente', icon: 'edit', onClick: () => openEditClientModal(row) },
                        { label: 'Eliminar Cliente', icon: 'delete', variant: 'destructive', onClick: () => openDeleteClientConfirm(row) }
                    ]"
                />
            </template>

            <!-- Expanded row showing Client Branches -->
            <template #expanded-row="{ row }">
                <tr v-if="expandedClientIds.includes(row.id)" class="bg-zinc-50/80 dark:bg-zinc-800/40 border-b border-zinc-200 dark:border-zinc-800">
                    <td colspan="6" class="p-4 pl-12">
                        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                                    <Building2 class="h-4 w-4 text-indigo-600" />
                                    Sucursales de {{ row.name }}
                                </h4>
                                <Button size="sm" variant="outline" @click="openCreateBranchModal(row)" class="rounded-lg text-xs h-8">
                                    <Plus class="h-3 w-3 mr-1" /> Agregar Sucursal
                                </Button>
                            </div>

                            <table class="w-full text-xs text-left">
                                <thead class="bg-zinc-100 dark:bg-zinc-800 text-zinc-500 font-semibold uppercase">
                                    <tr>
                                        <th class="py-2 px-3">Código</th>
                                        <th class="py-2 px-3">Nombre de Sucursal</th>
                                        <th class="py-2 px-3">Contacto</th>
                                        <th class="py-2 px-3">Teléfono / Correo</th>
                                        <th class="py-2 px-3">Dirección</th>
                                        <th class="py-2 px-3 text-center">Estado</th>
                                        <th class="py-2 px-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                    <tr v-for="branch in row.branches" :key="branch.id">
                                        <td class="py-2.5 px-3 font-mono font-medium text-indigo-600 dark:text-indigo-400">
                                            {{ branch.code || '-' }}
                                        </td>
                                        <td class="py-2.5 px-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ branch.name }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="text-zinc-800 dark:text-zinc-200 font-medium block">{{ branch.contact_name || 'Sin contacto' }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 text-zinc-600 dark:text-zinc-400">
                                            <div class="flex flex-col gap-0.5">
                                                <span v-if="branch.phone" class="flex items-center gap-1">
                                                    <Phone class="h-3 w-3 text-zinc-400 shrink-0" /> {{ branch.phone }}
                                                </span>
                                                <span v-if="branch.email" class="flex items-center gap-1 text-zinc-500">
                                                    <Mail class="h-3 w-3 text-zinc-400 shrink-0" /> {{ branch.email }}
                                                </span>
                                                <span v-if="!branch.phone && !branch.email" class="text-zinc-400 italic">N/A</span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-3 text-zinc-600 dark:text-zinc-400 max-w-[200px] truncate" :title="branch.address || ''">
                                            <span v-if="branch.address" class="flex items-center gap-1">
                                                <MapPin class="h-3 w-3 text-zinc-400 shrink-0" /> {{ branch.address }}
                                            </span>
                                            <span v-else class="text-zinc-400 italic">No especificada</span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <Badge :variant="branch.is_active ? 'default' : 'secondary'" class="text-[10px] py-0">
                                                {{ branch.is_active ? 'Activa' : 'Inactiva' }}
                                            </Badge>
                                        </td>
                                        <td class="py-2.5 px-3 text-right space-x-1">
                                            <Button size="icon" variant="ghost" class="h-7 w-7 text-indigo-600" @click="openEditBranchModal(row, branch)">
                                                <Edit class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button size="icon" variant="ghost" class="h-7 w-7 text-red-500" @click="openDeleteBranchConfirm(branch)">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </td>
                                    </tr>
                                    <tr v-if="!row.branches || row.branches.length === 0">
                                        <td colspan="7" class="py-4 text-center text-zinc-400 italic">
                                            Este cliente no tiene sucursales registradas (aplica como cliente único / matriz).
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </template>
        </DataTable>
    </div>

    <!-- Client Form Modal -->
    <Dialog :open="showClientModal" @update:open="showClientModal = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ isEditingClient ? 'Editar Cliente' : 'Nuevo Cliente' }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitClientForm" class="space-y-4 py-2">
                <div class="space-y-1">
                    <Label for="client_code">Código de Cliente</Label>
                    <Input id="client_code" v-model="clientForm.code" placeholder="Ej: CLI-001" required />
                    <span v-if="clientForm.errors.code" class="text-xs text-red-500">{{ clientForm.errors.code }}</span>
                </div>

                <div class="space-y-1">
                    <Label for="client_name">Razón Social / Nombre Comercial</Label>
                    <Input id="client_name" v-model="clientForm.name" placeholder="Ej: Industrias del Norte S.A." required />
                    <span v-if="clientForm.errors.name" class="text-xs text-red-500">{{ clientForm.errors.name }}</span>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="client_active" v-model="clientForm.is_active" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                    <Label for="client_active">Cliente Activo</Label>
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showClientModal = false">Cancelar</Button>
                    <Button type="submit" :disabled="clientForm.processing" class="bg-indigo-600 text-white hover:bg-indigo-700">
                        {{ isEditingClient ? 'Guardar Cambios' : 'Crear Cliente' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Branch Form Modal -->
    <Dialog :open="showBranchModal" @update:open="showBranchModal = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditingBranch ? 'Editar Sucursal de Cliente' : `Nueva Sucursal (${parentClientForBranch?.name})` }}
                </DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitBranchForm" class="space-y-4 py-2">
                <div class="space-y-1">
                    <Label for="branch_code">Código de Sucursal (Opcional)</Label>
                    <Input id="branch_code" v-model="branchForm.code" placeholder="Ej: SUC-MTY" />
                </div>

                <div class="space-y-1">
                    <Label for="branch_name">Nombre de la Sucursal</Label>
                    <Input id="branch_name" v-model="branchForm.name" placeholder="Ej: Planta Monterrey Poniente" required />
                    <span v-if="branchForm.errors.name" class="text-xs text-red-500">{{ branchForm.errors.name }}</span>
                </div>

                <div class="space-y-1">
                    <Label for="branch_contact">Nombre de Contacto</Label>
                    <Input id="branch_contact" v-model="branchForm.contact_name" placeholder="Ej: Lic. Roberto Garza" />
                    <span v-if="branchForm.errors.contact_name" class="text-xs text-red-500">{{ branchForm.errors.contact_name }}</span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <Label for="branch_phone">Teléfono</Label>
                        <Input id="branch_phone" v-model="branchForm.phone" placeholder="Ej: 818-123-4568" />
                        <span v-if="branchForm.errors.phone" class="text-xs text-red-500">{{ branchForm.errors.phone }}</span>
                    </div>
                    <div class="space-y-1">
                        <Label for="branch_email">Correo Electrónico</Label>
                        <Input id="branch_email" type="email" v-model="branchForm.email" placeholder="contacto@sucursal.com" />
                        <span v-if="branchForm.errors.email" class="text-xs text-red-500">{{ branchForm.errors.email }}</span>
                    </div>
                </div>

                <div class="space-y-1">
                    <Label for="branch_address">Dirección</Label>
                    <Input id="branch_address" v-model="branchForm.address" placeholder="Ej: Parque Industrial Mitras Lote 4" />
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="branch_active" v-model="branchForm.is_active" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                    <Label for="branch_active">Sucursal Activa</Label>
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showBranchModal = false">Cancelar</Button>
                    <Button type="submit" :disabled="branchForm.processing" class="bg-indigo-600 text-white hover:bg-indigo-700">
                        {{ isEditingBranch ? 'Guardar Cambios' : 'Guardar Sucursal' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialogs -->
    <ConfirmDeleteDialog
        :open="showDeleteClientDialog"
        @update:open="showDeleteClientDialog = $event"
        title="¿Eliminar Cliente?"
        :description="`¿Estás seguro de enviar al cliente '${selectedClient?.name}' a la papelera? Se ocultarán sus sucursales asociadas.`"
        @confirm="confirmDeleteClient"
    />

    <ConfirmDeleteDialog
        :open="showDeleteBranchDialog"
        @update:open="showDeleteBranchDialog = $event"
        title="¿Eliminar Sucursal del Cliente?"
        :description="`¿Estás seguro de eliminar la sucursal '${selectedBranch?.name}'?`"
        @confirm="confirmDeleteBranch"
    />
</template>
