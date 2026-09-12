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
import { Plus, UserCheck, Shield, Building } from '@lucide/vue';

interface Role {
    id: number;
    name: string;
}

interface Branch {
    id: number;
    name: string;
}

interface UserItem {
    id: number;
    name: string;
    email: string;
    roles: Role[];
    branches: Branch[];
}

const props = defineProps<{
    users: {
        data: UserItem[];
        current_page?: number;
        last_page?: number;
        per_page?: number;
        total?: number;
        from?: number | null;
        to?: number | null;
    };
    roles: Role[];
    branches: Branch[];
    filters?: {
        search?: string;
        per_page?: number;
    };
}>();

const columns: ColumnDef[] = [
    { key: 'name', label: 'Usuario', sortable: true },
    { key: 'email', label: 'Correo Electrónico', sortable: true },
    { key: 'role_name', label: 'Rol Asignado', sortable: true },
    { key: 'branches_count', label: 'Sucursales Permitidas' },
    { key: 'actions', label: 'Acciones', align: 'right' },
];

const userRows = computed(() => {
    return props.users.data.map(u => ({
        ...u,
        role_name: u.roles[0]?.name || 'Sin Rol',
        branches_count: u.roles.some((r: Role) => r.name === 'admin') ? 'Todas (Admin)' : `${u.branches.length} asignada(s)`,
    }));
});

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedUser = ref<UserItem | null>(null);
const isEditing = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: props.roles[0]?.name || 'encargado',
    branch_ids: [] as number[],
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedUser.value = null;
    form.reset();
    form.clearErrors();
    showFormModal.value = true;
};

const openEditModal = (u: UserItem) => {
    isEditing.value = true;
    selectedUser.value = u;
    form.clearErrors();
    form.name = u.name;
    form.email = u.email;
    form.password = '';
    form.role = u.roles[0]?.name || 'encargado';
    form.branch_ids = u.branches.map(b => b.id);
    showFormModal.value = true;
};

const openDeleteConfirm = (u: UserItem) => {
    selectedUser.value = u;
    showDeleteDialog.value = true;
};

const toggleBranch = (branchId: number) => {
    const idx = form.branch_ids.indexOf(branchId);
    if (idx > -1) {
        form.branch_ids.splice(idx, 1);
    } else {
        form.branch_ids.push(branchId);
    }
};

const submitForm = () => {
    if (isEditing.value && selectedUser.value) {
        form.put(`/admin/users/${selectedUser.value.id}`, {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/admin/users', {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = () => {
    if (selectedUser.value) {
        router.delete(`/admin/users/${selectedUser.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                selectedUser.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Usuarios" />

    <div class="p-6 space-y-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <UserCheck class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Gestión de Usuarios y Sucursales Asignadas
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Administra las cuentas de usuario, sus roles (Admin vs Encargado) y las sucursales sobre las que tienen autorización.
                </p>
            </div>
            <Button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow">
                <Plus class="h-4 w-4 mr-2" /> Nuevo Usuario
            </Button>
        </div>

        <!-- DataTable Component -->
        <DataTable
            :columns="columns"
            :data="userRows"
            :pagination="users"
            :filters="filters"
            searchPlaceholder="Buscar usuario por nombre, correo, rol..."
        >
            <template #cell-name="{ row }">
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ row.name }}</span>
            </template>

            <template #cell-email="{ row }">
                <span class="text-zinc-600 dark:text-zinc-400">{{ row.email }}</span>
            </template>

            <template #cell-role_name="{ row }">
                <Badge
                    v-for="r in row.roles"
                    :key="r.id"
                    :class="r.name === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'"
                >
                    <Shield class="h-3 w-3 mr-1 inline" /> {{ r.name }}
                </Badge>
            </template>

            <template #cell-branches_count="{ row }">
                <div v-if="row.roles.some((r: Role) => r.name === 'admin')" class="text-xs font-semibold text-purple-600 dark:text-purple-400">
                    Todas las Sucursales (Admin)
                </div>
                <div v-else-if="row.branches.length > 0" class="flex flex-wrap gap-1">
                    <Badge v-for="b in row.branches" :key="b.id" variant="outline" class="text-xs">
                        <Building class="h-3 w-3 mr-1 inline" /> {{ b.name }}
                    </Badge>
                </div>
                <span v-else class="text-zinc-400 italic text-xs">Sin sucursales asignadas</span>
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
                <DialogTitle>{{ isEditing ? 'Editar Usuario' : 'Nuevo Usuario' }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitForm" class="space-y-4 py-2">
                <div class="space-y-1">
                    <Label for="u_name">Nombre Completo</Label>
                    <Input id="u_name" v-model="form.name" placeholder="Ej: Carlos Encargado" required />
                </div>

                <div class="space-y-1">
                    <Label for="u_email">Correo Electrónico</Label>
                    <Input id="u_email" type="email" v-model="form.email" placeholder="usuario@empresa.com" required />
                    <span v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</span>
                </div>

                <div class="space-y-1">
                    <Label for="u_password">Contraseña {{ isEditing ? '(dejar en blanco para no cambiar)' : '' }}</Label>
                    <Input id="u_password" type="password" v-model="form.password" :required="!isEditing" placeholder="••••••••" />
                </div>

                <div class="space-y-1">
                    <Label for="u_role">Rol en el Sistema</Label>
                    <select
                        id="u_role"
                        v-model="form.role"
                        class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 text-sm"
                        required
                    >
                        <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                    </select>
                </div>

                <div v-if="form.role !== 'admin'" class="space-y-2 pt-2">
                    <Label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Sucursales Permitidas (Encargado)</Label>
                    <div class="space-y-1.5 max-h-40 overflow-y-auto border border-zinc-200 dark:border-zinc-800 p-2 rounded-md bg-zinc-50 dark:bg-zinc-800/40">
                        <label v-for="b in branches" :key="b.id" class="flex items-center gap-2 text-sm cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 p-1 rounded">
                            <input
                                type="checkbox"
                                :checked="form.branch_ids.includes(b.id)"
                                @change="toggleBranch(b.id)"
                                class="rounded text-indigo-600 focus:ring-indigo-500"
                            />
                            <span>{{ b.name }}</span>
                        </label>
                    </div>
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showFormModal = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white hover:bg-indigo-700">
                        {{ isEditing ? 'Guardar Cambios' : 'Crear Usuario' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
        :open="showDeleteDialog"
        @update:open="showDeleteDialog = $event"
        title="¿Eliminar Usuario?"
        :description="`¿Estás seguro de eliminar el acceso del usuario '${selectedUser?.name}'?`"
        @confirm="confirmDelete"
    />
</template>
