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
import { Plus, Users, DollarSign, Building } from '@lucide/vue';

interface Branch {
    id: number;
    name: string;
}

interface Employee {
    id: number;
    branch_id: number;
    branch?: Branch;
    first_name: string;
    last_name: string;
    employee_code: string;
    base_hourly_rate: number | string;
    overtime_hourly_rate: number | string;
    is_active: boolean;
}

const props = defineProps<{
    employees: {
        data: Employee[];
        current_page?: number;
        last_page?: number;
        per_page?: number;
        total?: number;
        from?: number | null;
        to?: number | null;
    };
    branches: Branch[];
    filters?: {
        search?: string;
        branch_id?: string;
        per_page?: number;
    };
}>();

const columns: ColumnDef[] = [
    { key: 'employee_code', label: 'Código', sortable: true },
    { key: 'full_name', label: 'Empleado', sortable: true },
    { key: 'branch_name', label: 'Sucursal', sortable: true },
    { key: 'base_hourly_rate', label: 'Tarifa Hora Base', sortable: true },
    { key: 'overtime_hourly_rate', label: 'Tarifa Hora Extra', sortable: true },
    { key: 'is_active', label: 'Estado', sortable: true },
    { key: 'actions', label: 'Acciones', align: 'right' },
];

const employeeRows = computed(() => {
    return props.employees.data.map(emp => ({
        ...emp,
        full_name: `${emp.first_name} ${emp.last_name}`,
        branch_name: emp.branch?.name || 'N/A',
    }));
});

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedEmployee = ref<Employee | null>(null);
const isEditing = ref(false);

const form = useForm({
    branch_id: props.branches[0]?.id || '',
    first_name: '',
    last_name: '',
    employee_code: '',
    base_hourly_rate: '100.00',
    overtime_hourly_rate: '150.00',
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedEmployee.value = null;
    form.reset();
    form.clearErrors();
    showFormModal.value = true;
};

const openEditModal = (employee: Employee) => {
    isEditing.value = true;
    selectedEmployee.value = employee;
    form.clearErrors();
    form.branch_id = employee.branch_id;
    form.first_name = employee.first_name;
    form.last_name = employee.last_name;
    form.employee_code = employee.employee_code;
    form.base_hourly_rate = String(employee.base_hourly_rate);
    form.overtime_hourly_rate = String(employee.overtime_hourly_rate);
    form.is_active = employee.is_active;
    showFormModal.value = true;
};

const openDeleteConfirm = (employee: Employee) => {
    selectedEmployee.value = employee;
    showDeleteDialog.value = true;
};

const submitForm = () => {
    if (isEditing.value && selectedEmployee.value) {
        form.put(`/catalogs/employees/${selectedEmployee.value.id}`, {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/catalogs/employees', {
            onSuccess: () => {
                showFormModal.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = () => {
    if (selectedEmployee.value) {
        router.delete(`/catalogs/employees/${selectedEmployee.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                selectedEmployee.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Empleados" />

    <div class="p-6 space-y-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <Users class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Catálogo de Empleados y Salarios
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Gestiona los empleados por sucursal y asigna sus tarifas de salario por hora base y hora extra.
                </p>
            </div>
            <Button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow">
                <Plus class="h-4 w-4 mr-2" /> Nuevo Empleado
            </Button>
        </div>

        <!-- DataTable Component -->
        <DataTable
            :columns="columns"
            :data="employeeRows"
            :pagination="employees"
            :filters="filters"
            searchPlaceholder="Buscar por código, nombre, sucursal..."
        >
            <template #cell-employee_code="{ row }">
                <span class="font-mono font-medium text-indigo-600 dark:text-indigo-400">{{ row.employee_code }}</span>
            </template>

            <template #cell-full_name="{ row }">
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ row.full_name }}</span>
            </template>

            <template #cell-branch_name="{ row }">
                <span class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                    <Building class="h-3.5 w-3.5 text-zinc-400" />
                    {{ row.branch_name }}
                </span>
            </template>

            <template #cell-base_hourly_rate="{ row }">
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                    ${{ Number(row.base_hourly_rate).toFixed(2) }} / hr
                </span>
            </template>

            <template #cell-overtime_hourly_rate="{ row }">
                <span class="font-semibold text-amber-600 dark:text-amber-400">
                    ${{ Number(row.overtime_hourly_rate).toFixed(2) }} / hr
                </span>
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

    <!-- Form Modal -->
    <Dialog :open="showFormModal" @update:open="showFormModal = $event">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? 'Editar Empleado' : 'Nuevo Empleado' }}</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitForm" class="space-y-4 py-2">
                <div class="space-y-1">
                    <Label for="branch_id">Sucursal Asignada</Label>
                    <select
                        id="branch_id"
                        v-model="form.branch_id"
                        class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 text-sm text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required
                    >
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="first_name">Nombre(s)</Label>
                        <Input id="first_name" v-model="form.first_name" placeholder="Ej: Juan" required />
                    </div>
                    <div class="space-y-1">
                        <Label for="last_name">Apellidos</Label>
                        <Input id="last_name" v-model="form.last_name" placeholder="Ej: Pérez" required />
                    </div>
                </div>

                <div class="space-y-1">
                    <Label for="employee_code">Código de Empleado</Label>
                    <Input id="employee_code" v-model="form.employee_code" placeholder="Ej: EMP-001" required />
                    <span v-if="form.errors.employee_code" class="text-xs text-red-500">{{ form.errors.employee_code }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4 bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <div class="space-y-1">
                        <Label for="base_hourly_rate" class="text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                            <DollarSign class="h-3.5 w-3.5" /> Tarifa Hora Base ($)
                        </Label>
                        <Input id="base_hourly_rate" type="number" step="0.01" min="0" v-model="form.base_hourly_rate" required />
                    </div>

                    <div class="space-y-1">
                        <Label for="overtime_hourly_rate" class="text-amber-700 dark:text-amber-400 flex items-center gap-1">
                            <DollarSign class="h-3.5 w-3.5" /> Tarifa Hora Extra ($)
                        </Label>
                        <Input id="overtime_hourly_rate" type="number" step="0.01" min="0" v-model="form.overtime_hourly_rate" required />
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="is_active_emp" v-model="form.is_active" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                    <Label for="is_active_emp">Empleado Activo</Label>
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showFormModal = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white hover:bg-indigo-700">
                        {{ isEditing ? 'Guardar Cambios' : 'Registrar Empleado' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
        :open="showDeleteDialog"
        @update:open="showDeleteDialog = $event"
        title="¿Eliminar Empleado?"
        :description="`¿Estás seguro de enviar al empleado '${selectedEmployee?.first_name} ${selectedEmployee?.last_name}' a la papelera?`"
        @confirm="confirmDelete"
    />
</template>
