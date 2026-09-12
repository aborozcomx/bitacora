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
    DialogFooter,
    DialogDescription
} from '@/components/ui/dialog';
import { Plus, Hash, Lock, FileDigit, Info } from '@lucide/vue';

interface Folio {
    id: number;
    name: string;
    current_consecutive: number;
    description: string | null;
    is_active: boolean;
}

const props = defineProps<{
    folios: {
        data: Folio[];
        current_page?: number;
        last_page?: number;
        per_page?: number;
        total?: number;
        from?: number | null;
        to?: number | null;
    };
    filters?: {
        search?: string;
        per_page?: number;
    };
}>();

const columns: ColumnDef[] = [
    { key: 'name', label: 'Nombre / Prefijo', sortable: true },
    { key: 'current_consecutive', label: 'Consecutivo Actual', sortable: true, align: 'center' },
    { key: 'description', label: 'Descripción / Uso', sortable: true },
    { key: 'is_active', label: 'Estado', sortable: true },
    { key: 'actions', label: 'Acciones', align: 'right' },
];

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedFolio = ref<Folio | null>(null);
const isEditing = ref(false);

const form = useForm({
    name: '',
    description: '',
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedFolio.value = null;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    showFormModal.value = true;
};

const openEditModal = (folio: Folio) => {
    isEditing.value = true;
    selectedFolio.value = folio;
    form.clearErrors();
    form.name = folio.name;
    form.description = folio.description || '';
    form.is_active = Boolean(folio.is_active);
    showFormModal.value = true;
};

const openDeleteConfirm = (folio: Folio) => {
    selectedFolio.value = folio;
    showDeleteDialog.value = true;
};

const submitForm = () => {
    if (isEditing.value && selectedFolio.value) {
        form.put(`/catalogs/folios/${selectedFolio.value.id}`, {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/catalogs/folios', {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = () => {
    if (selectedFolio.value) {
        router.delete(`/catalogs/folios/${selectedFolio.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                selectedFolio.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Catálogo de Folios" />

    <div class="p-6 space-y-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <FileDigit class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Catálogo de Folios y Consecutivos
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Administra las series de folios disponibles para las bitácoras. Los números consecutivos son estrictamente numéricos y se actualizan automáticamente al emitir bitácoras.
                </p>
            </div>
            <Button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow">
                <Plus class="h-4 w-4 mr-2" /> Nuevo Folio
            </Button>
        </div>

        <!-- DataTable Component -->
        <DataTable
            :columns="columns"
            :data="folios.data"
            :pagination="folios"
            :filters="filters"
            :default-per-page="15"
            searchPlaceholder="Buscar por nombre o descripción de folio..."
        >
            <template #cell-name="{ row }">
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold font-mono bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    {{ row.name }}
                </span>
            </template>

            <template #cell-current_consecutive="{ row }">
                <div class="flex items-center justify-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                        <Hash class="h-3 w-3 text-zinc-400" />
                        {{ Number(row.current_consecutive) }}
                    </span>
                </div>
            </template>

            <template #cell-description="{ row }">
                <span class="text-zinc-600 dark:text-zinc-400 text-sm">{{ row.description || 'Sin descripción' }}</span>
            </template>

            <template #cell-is_active="{ row }">
                <Badge :variant="row.is_active ? 'default' : 'secondary'" :class="row.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border-emerald-200' : ''">
                    {{ row.is_active ? 'Activo' : 'Inactivo' }}
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

    <!-- Form Modal (Create / Edit) -->
    <Dialog :open="showFormModal" @update:open="showFormModal = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? 'Editar Folio' : 'Nuevo Folio' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Modifica el nombre o descripción de la serie de folio.' : 'Registra un nuevo prefijo o serie de folio en el catálogo.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="space-y-4 py-2">
                <!-- Nombre del Folio (Editable) -->
                <div class="space-y-1">
                    <Label for="folio_name" class="text-sm font-medium">Nombre de la Serie / Prefijo *</Label>
                    <Input
                        id="folio_name"
                        v-model="form.name"
                        placeholder="Ej: BIT, ICC, SERV, MANT"
                        required
                        maxlength="50"
                        class="uppercase font-mono font-bold"
                    />
                    <span v-if="form.errors.name" class="text-xs text-red-500 font-medium">{{ form.errors.name }}</span>
                </div>

                <!-- Consecutivo (SOLO LECTURA - NUMERICO) -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <Label for="folio_consecutive_display" class="text-sm font-medium flex items-center gap-1 text-zinc-700 dark:text-zinc-300">
                            <Lock class="h-3.5 w-3.5 text-zinc-400" />
                            Consecutivo Numérico (Solo lectura)
                        </Label>
                        <span class="text-xs text-zinc-400 font-mono">Solo lectura</span>
                    </div>
                    <div class="relative">
                        <Input
                            id="folio_consecutive_display"
                            type="number"
                            :value="isEditing && selectedFolio ? Number(selectedFolio.current_consecutive) : 0"
                            readonly
                            disabled
                            class="bg-zinc-100 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-300 font-mono font-bold cursor-not-allowed pl-8 border-dashed"
                        />
                        <Hash class="h-4 w-4 text-zinc-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 flex items-start gap-1 pt-1">
                        <Info class="h-3.5 w-3.5 text-indigo-500 shrink-0 mt-0.5" />
                        <span>El consecutivo es numérico y se incrementa automáticamente a través de la creación de bitácoras.</span>
                    </p>
                </div>

                <!-- Descripción -->
                <div class="space-y-1">
                    <Label for="folio_desc" class="text-sm font-medium">Descripción o Propósito (Opcional)</Label>
                    <textarea
                        id="folio_desc"
                        v-model="form.description"
                        rows="3"
                        class="w-full rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Folio utilizado para bitácoras de obra civil y proyectos..."
                    ></textarea>
                    <span v-if="form.errors.description" class="text-xs text-red-500 font-medium">{{ form.errors.description }}</span>
                </div>

                <!-- Estado Activo -->
                <div class="flex items-center gap-2 pt-1">
                    <input
                        type="checkbox"
                        id="folio_active"
                        v-model="form.is_active"
                        class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                    />
                    <Label for="folio_active" class="text-sm cursor-pointer">Folio Activo (disponible para nuevas bitácoras)</Label>
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showFormModal = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white hover:bg-indigo-700">
                        {{ isEditing ? 'Guardar Cambios' : 'Crear Folio' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
        :open="showDeleteDialog"
        @update:open="showDeleteDialog = $event"
        title="¿Eliminar Folio?"
        :description="`¿Estás seguro de enviar la serie de folio '${selectedFolio?.name}' a la papelera?`"
        @confirm="confirmDelete"
    />
</template>
