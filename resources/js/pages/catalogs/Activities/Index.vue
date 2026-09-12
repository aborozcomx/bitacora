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
import { Plus, ListCheck } from '@lucide/vue';

interface ActivityType {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
}

const props = defineProps<{
    activities: {
        data: ActivityType[];
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
    { key: 'name', label: 'Nombre de Actividad', sortable: true },
    { key: 'description', label: 'Descripción / Alcance', sortable: true },
    { key: 'is_active', label: 'Estado', sortable: true },
    { key: 'actions', label: 'Acciones', align: 'right' },
];

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedActivity = ref<ActivityType | null>(null);
const isEditing = ref(false);

const form = useForm({
    name: '',
    description: '',
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedActivity.value = null;
    form.reset();
    form.clearErrors();
    showFormModal.value = true;
};

const openEditModal = (act: ActivityType) => {
    isEditing.value = true;
    selectedActivity.value = act;
    form.clearErrors();
    form.name = act.name;
    form.description = act.description || '';
    form.is_active = act.is_active;
    showFormModal.value = true;
};

const openDeleteConfirm = (act: ActivityType) => {
    selectedActivity.value = act;
    showDeleteDialog.value = true;
};

const submitForm = () => {
    if (isEditing.value && selectedActivity.value) {
        form.put(`/catalogs/activities/${selectedActivity.value.id}`, {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/catalogs/activities', {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = () => {
    if (selectedActivity.value) {
        router.delete(`/catalogs/activities/${selectedActivity.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                selectedActivity.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Actividades" />

    <div class="p-6 space-y-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <ListCheck class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Catálogo de Actividades
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Define los tipos y conceptos de actividades realizables en las bitácoras diarias.
                </p>
            </div>
            <Button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow">
                <Plus class="h-4 w-4 mr-2" /> Nueva Actividad
            </Button>
        </div>

        <!-- DataTable Component -->
        <DataTable
            :columns="columns"
            :data="activities.data"
            :pagination="activities"
            :filters="filters"
            searchPlaceholder="Buscar por nombre o descripción de actividad..."
        >
            <template #cell-name="{ row }">
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ row.name }}</span>
            </template>

            <template #cell-description="{ row }">
                <span class="text-zinc-600 dark:text-zinc-400">{{ row.description || 'Sin descripción' }}</span>
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
                <DialogTitle>{{ isEditing ? 'Editar Actividad' : 'Nueva Actividad' }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitForm" class="space-y-4 py-2">
                <div class="space-y-1">
                    <Label for="act_name">Nombre de la Actividad</Label>
                    <Input id="act_name" v-model="form.name" placeholder="Ej: Mantenimiento Preventivo" required />
                </div>

                <div class="space-y-1">
                    <Label for="act_desc">Descripción</Label>
                    <textarea
                        id="act_desc"
                        v-model="form.description"
                        rows="3"
                        class="w-full rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Detalle o instrucciones generales..."
                    ></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="act_active" v-model="form.is_active" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                    <Label for="act_active">Actividad Activa</Label>
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showFormModal = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white hover:bg-indigo-700">
                        {{ isEditing ? 'Guardar Cambios' : 'Crear Actividad' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
        :open="showDeleteDialog"
        @update:open="showDeleteDialog = $event"
        title="¿Eliminar Tipo de Actividad?"
        :description="`¿Estás seguro de enviar la actividad '${selectedActivity?.name}' a la papelera?`"
        @confirm="confirmDelete"
    />
</template>
