<script setup lang="ts">
import { ref, computed } from 'vue';
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
import { Plus, ShieldCheck, Key } from '@lucide/vue';

interface Permission {
    id: number;
    name: string;
}

interface RoleItem {
    id: number;
    name: string;
    permissions: Permission[];
}

const props = defineProps<{
    roles: RoleItem[];
    permissions: Permission[];
}>();

const columns: ColumnDef[] = [
    { key: 'name', label: 'Nombre del Rol', sortable: true },
    { key: 'permissions_count', label: 'Permisos Asignados' },
    { key: 'actions', label: 'Acciones', align: 'right' },
];

const roleRows = computed(() => {
    return props.roles.map(r => ({
        ...r,
        permissions_count: r.permissions.map(p => p.name).join(', '),
    }));
});

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedRole = ref<RoleItem | null>(null);
const isEditing = ref(false);

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedRole.value = null;
    form.reset();
    form.clearErrors();
    showFormModal.value = true;
};

const openEditModal = (r: RoleItem) => {
    isEditing.value = true;
    selectedRole.value = r;
    form.clearErrors();
    form.name = r.name;
    form.permissions = r.permissions.map(p => p.name);
    showFormModal.value = true;
};

const openDeleteConfirm = (r: RoleItem) => {
    selectedRole.value = r;
    showDeleteDialog.value = true;
};

const togglePermission = (permName: string) => {
    const idx = form.permissions.indexOf(permName);
    if (idx > -1) {
        form.permissions.splice(idx, 1);
    } else {
        form.permissions.push(permName);
    }
};

const submitForm = () => {
    if (isEditing.value && selectedRole.value) {
        form.put(`/admin/roles/${selectedRole.value.id}`, {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/admin/roles', {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = () => {
    if (selectedRole.value) {
        router.delete(`/admin/roles/${selectedRole.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                selectedRole.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Roles y Permisos" />

    <div class="p-6 space-y-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <ShieldCheck class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Autoadministración de Roles y Permisos (Spatie)
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Crea roles personalizados y asigna permisos específicos para controlar el acceso a la plataforma.
                </p>
            </div>
            <Button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow">
                <Plus class="h-4 w-4 mr-2" /> Nuevo Rol
            </Button>
        </div>

        <!-- DataTable Component -->
        <DataTable
            :columns="columns"
            :data="roleRows"
            searchPlaceholder="Buscar rol o permiso..."
        >
            <template #cell-name="{ row }">
                <span class="font-semibold text-zinc-900 dark:text-zinc-100 capitalize">{{ row.name }}</span>
            </template>

            <template #cell-permissions_count="{ row }">
                <div class="flex flex-wrap gap-1.5">
                    <Badge v-for="p in row.permissions" :key="p.id" variant="secondary" class="text-xs">
                        <Key class="h-3 w-3 mr-1 inline" /> {{ p.name }}
                    </Badge>
                    <span v-if="row.permissions.length === 0" class="text-zinc-400 italic text-xs">Sin permisos</span>
                </div>
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
                <DialogTitle>{{ isEditing ? 'Editar Rol' : 'Nuevo Rol' }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitForm" class="space-y-4 py-2">
                <div class="space-y-1">
                    <Label for="r_name">Nombre del Rol</Label>
                    <Input id="r_name" v-model="form.name" placeholder="Ej: supervisor" required />
                </div>

                <div class="space-y-2 pt-2">
                    <Label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Asignar Permisos</Label>
                    <div class="space-y-1.5 max-h-48 overflow-y-auto border border-zinc-200 dark:border-zinc-800 p-3 rounded-md bg-zinc-50 dark:bg-zinc-800/40">
                        <label v-for="p in permissions" :key="p.id" class="flex items-center gap-2 text-sm cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 p-1.5 rounded">
                            <input
                                type="checkbox"
                                :checked="form.permissions.includes(p.name)"
                                @change="togglePermission(p.name)"
                                class="rounded text-indigo-600 focus:ring-indigo-500"
                            />
                            <span class="font-mono text-xs">{{ p.name }}</span>
                        </label>
                    </div>
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showFormModal = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white hover:bg-indigo-700">
                        {{ isEditing ? 'Guardar Cambios' : 'Crear Rol' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
        :open="showDeleteDialog"
        @update:open="showDeleteDialog = $event"
        title="¿Eliminar Rol?"
        :description="`¿Estás seguro de eliminar el rol '${selectedRole?.name}'?`"
        @confirm="confirmDelete"
    />
</template>
