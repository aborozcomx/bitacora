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
import { Plus, Building2, Phone, MapPin } from '@lucide/vue';

interface Branch {
    id: number;
    name: string;
    code: string;
    address: string | null;
    phone: string | null;
    is_active: boolean;
}

const props = defineProps<{
    branches: {
        data: Branch[];
    };
}>();

const columns: ColumnDef[] = [
    { key: 'code', label: 'Código', sortable: true },
    { key: 'name', label: 'Nombre de Sucursal', sortable: true },
    { key: 'address', label: 'Dirección', sortable: true },
    { key: 'phone', label: 'Teléfono', sortable: true },
    { key: 'is_active', label: 'Estado', sortable: true },
    { key: 'actions', label: 'Acciones', align: 'right' },
];

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedBranch = ref<Branch | null>(null);
const isEditing = ref(false);

const form = useForm({
    name: '',
    code: '',
    address: '',
    phone: '',
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedBranch.value = null;
    form.reset();
    form.clearErrors();
    showFormModal.value = true;
};

const openEditModal = (branch: Branch) => {
    isEditing.value = true;
    selectedBranch.value = branch;
    form.clearErrors();
    form.name = branch.name;
    form.code = branch.code;
    form.address = branch.address || '';
    form.phone = branch.phone || '';
    form.is_active = branch.is_active;
    showFormModal.value = true;
};

const openDeleteConfirm = (branch: Branch) => {
    selectedBranch.value = branch;
    showDeleteDialog.value = true;
};

const submitForm = () => {
    if (isEditing.value && selectedBranch.value) {
        form.put(`/catalogs/branches/${selectedBranch.value.id}`, {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/catalogs/branches', {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = () => {
    if (selectedBranch.value) {
        router.delete(`/catalogs/branches/${selectedBranch.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                selectedBranch.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Sucursales" />

    <div class="p-6 space-y-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <Building2 class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Catálogo de Sucursales
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Administra las sucursales de la empresa para la gestión de bitácoras y empleados.
                </p>
            </div>
            <Button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow">
                <Plus class="h-4 w-4 mr-2" /> Nueva Sucursal
            </Button>
        </div>

        <!-- DataTable Component -->
        <DataTable
            :columns="columns"
            :data="branches.data"
            searchPlaceholder="Buscar sucursal por código, nombre, dirección..."
        >
            <template #cell-code="{ row }">
                <span class="font-mono font-medium text-indigo-600 dark:text-indigo-400">{{ row.code }}</span>
            </template>

            <template #cell-name="{ row }">
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ row.name }}</span>
            </template>

            <template #cell-address="{ row }">
                <span v-if="row.address" class="flex items-center gap-1.5 text-zinc-600 dark:text-zinc-400">
                    <MapPin class="h-3.5 w-3.5 shrink-0 text-zinc-400" /> {{ row.address }}
                </span>
                <span v-else class="text-zinc-400 italic">No especificada</span>
            </template>

            <template #cell-phone="{ row }">
                <span v-if="row.phone" class="flex items-center gap-1.5 text-zinc-600 dark:text-zinc-400">
                    <Phone class="h-3.5 w-3.5 shrink-0 text-zinc-400" /> {{ row.phone }}
                </span>
                <span v-else class="text-zinc-400 italic">N/A</span>
            </template>

            <template #cell-is_active="{ row }">
                <Badge :variant="row.is_active ? 'default' : 'secondary'" :class="row.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border-emerald-200' : ''">
                    {{ row.is_active ? 'Activa' : 'Inactiva' }}
                </Badge>
            </template>

            <template #cell-actions="{ row }">
                <ActionsDropdown
                    :actions="[
                        { label: 'Editar', icon: 'edit', onClick: () => openEditModal(row) },
                        { label: 'Eliminar', icon: 'delete', variant: 'destructive', onClick: () => openDeleteConfirm(row) }
                    ]"
                />
            </template>
        </DataTable>
    </div>

    <!-- Form Modal -->
    <Dialog :open="showFormModal" @update:open="showFormModal = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? 'Editar Sucursal' : 'Nueva Sucursal' }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitForm" class="space-y-4 py-2">
                <div class="space-y-1">
                    <Label for="code">Código de Sucursal</Label>
                    <Input id="code" v-model="form.code" placeholder="Ej: SUC-001" required />
                    <span v-if="form.errors.code" class="text-xs text-red-500">{{ form.errors.code }}</span>
                </div>

                <div class="space-y-1">
                    <Label for="name">Nombre de Sucursal</Label>
                    <Input id="name" v-model="form.name" placeholder="Ej: Sucursal Centro" required />
                    <span v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</span>
                </div>

                <div class="space-y-1">
                    <Label for="address">Dirección</Label>
                    <Input id="address" v-model="form.address" placeholder="Ej: Av. Juárez 100, Centro" />
                </div>

                <div class="space-y-1">
                    <Label for="phone">Teléfono de Contacto</Label>
                    <Input id="phone" v-model="form.phone" placeholder="Ej: 555-100-2000" />
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="is_active" v-model="form.is_active" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                    <Label for="is_active">Sucursal Activa</Label>
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showFormModal = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white hover:bg-indigo-700">
                        {{ isEditing ? 'Guardar Cambios' : 'Crear Sucursal' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
        :open="showDeleteDialog"
        @update:open="showDeleteDialog = $event"
        title="¿Eliminar Sucursal?"
        :description="`¿Estás seguro de enviar la sucursal '${selectedBranch?.name}' a la papelera (SoftDelete)?`"
        @confirm="confirmDelete"
    />
</template>
