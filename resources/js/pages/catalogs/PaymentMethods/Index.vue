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
import { Plus, CreditCard, Wallet, Landmark } from '@lucide/vue';

interface PaymentMethod {
    id: number;
    name: string;
    slug: string;
    requires_card_details: boolean;
    is_active: boolean;
}

interface CardType {
    id: number;
    name: string;
    is_active: boolean;
}

interface PaymentCard {
    id: number;
    alias: string;
    payment_method_id: number;
    card_type_id: number | null;
    bank_name: string | null;
    card_number_masked: string | null;
    is_active: boolean;
    payment_method?: PaymentMethod;
    card_type?: CardType;
}

const props = defineProps<{
    paymentMethods: PaymentMethod[];
    cardTypes: CardType[];
    paymentCards: PaymentCard[];
}>();

const cardColumns: ColumnDef[] = [
    { key: 'alias', label: 'Alias', sortable: true },
    { key: 'payment_method_name', label: 'Método de Pago', sortable: true },
    { key: 'bank_name', label: 'Banco', sortable: true },
    { key: 'card_type_name', label: 'Tipo Tarjeta', sortable: true },
    { key: 'card_number_masked', label: 'Número / Referencia', sortable: true },
    { key: 'actions', label: 'Acciones', align: 'right' },
];

const methodColumns: ColumnDef[] = [
    { key: 'name', label: 'Método de Pago', sortable: true },
    { key: 'slug', label: 'Clave Slug', sortable: true },
    { key: 'requires_card_details', label: 'Requiere Tarjeta', sortable: true },
    { key: 'actions', label: 'Acciones', align: 'right' },
];

const cardTypeColumns: ColumnDef[] = [
    { key: 'name', label: 'Nombre de Tipo', sortable: true },
    { key: 'is_active', label: 'Estado', sortable: true },
    { key: 'actions', label: 'Acciones', align: 'right' },
];

const paymentCardRows = computed(() => {
    return props.paymentCards.map(c => ({
        ...c,
        payment_method_name: c.payment_method?.name || 'N/A',
        card_type_name: c.card_type?.name || '-',
    }));
});

const activeTab = ref<'cards' | 'methods' | 'types'>('cards');

const showMethodModal = ref(false);
const showCardTypeModal = ref(false);
const showCardModal = ref(false);
const showDeleteDialog = ref(false);

const deleteType = ref<'method' | 'type' | 'card'>('method');
const selectedItem = ref<any>(null);
const isEditing = ref(false);

const methodForm = useForm({
    name: '',
    requires_card_details: false,
    is_active: true,
});

const cardTypeForm = useForm({
    name: '',
    is_active: true,
});

const cardForm = useForm({
    alias: '',
    payment_method_id: props.paymentMethods[0]?.id || '',
    card_type_id: '' as string | number,
    bank_name: '',
    card_number_masked: '',
    is_active: true,
});

const openCreateMethod = () => {
    isEditing.value = false;
    methodForm.reset();
    showMethodModal.value = true;
};
const openEditMethod = (m: PaymentMethod) => {
    isEditing.value = true;
    selectedItem.value = m;
    methodForm.name = m.name;
    methodForm.requires_card_details = m.requires_card_details;
    methodForm.is_active = m.is_active;
    showMethodModal.value = true;
};
const submitMethod = () => {
    if (isEditing.value && selectedItem.value) {
        methodForm.put(`/catalogs/payment-methods/method/${selectedItem.value.id}`, {
            onSuccess: () => showMethodModal.value = false,
        });
    } else {
        methodForm.post('/catalogs/payment-methods/method', {
            onSuccess: () => showMethodModal.value = false,
        });
    }
};

const openCreateCardType = () => {
    isEditing.value = false;
    cardTypeForm.reset();
    showCardTypeModal.value = true;
};
const openEditCardType = (ct: CardType) => {
    isEditing.value = true;
    selectedItem.value = ct;
    cardTypeForm.name = ct.name;
    cardTypeForm.is_active = ct.is_active;
    showCardTypeModal.value = true;
};
const submitCardType = () => {
    if (isEditing.value && selectedItem.value) {
        cardTypeForm.put(`/catalogs/payment-methods/card-type/${selectedItem.value.id}`, {
            onSuccess: () => showCardTypeModal.value = false,
        });
    } else {
        cardTypeForm.post('/catalogs/payment-methods/card-type', {
            onSuccess: () => showCardTypeModal.value = false,
        });
    }
};

const openCreateCard = () => {
    isEditing.value = false;
    cardForm.reset();
    showCardModal.value = true;
};
const openEditCard = (card: PaymentCard) => {
    isEditing.value = true;
    selectedItem.value = card;
    cardForm.alias = card.alias;
    cardForm.payment_method_id = card.payment_method_id;
    cardForm.card_type_id = card.card_type_id || '';
    cardForm.bank_name = card.bank_name || '';
    cardForm.card_number_masked = card.card_number_masked || '';
    cardForm.is_active = card.is_active;
    showCardModal.value = true;
};
const submitCard = () => {
    if (isEditing.value && selectedItem.value) {
        cardForm.put(`/catalogs/payment-methods/card/${selectedItem.value.id}`, {
            onSuccess: () => showCardModal.value = false,
        });
    } else {
        cardForm.post('/catalogs/payment-methods/card', {
            onSuccess: () => showCardModal.value = false,
        });
    }
};

const openDelete = (type: 'method' | 'type' | 'card', item: any) => {
    deleteType.value = type;
    selectedItem.value = item;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (!selectedItem.value) return;

    let url = '';
    if (deleteType.value === 'method') url = `/catalogs/payment-methods/method/${selectedItem.value.id}`;
    if (deleteType.value === 'type') url = `/catalogs/payment-methods/card-type/${selectedItem.value.id}`;
    if (deleteType.value === 'card') url = `/catalogs/payment-methods/card/${selectedItem.value.id}`;

    router.delete(url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedItem.value = null;
        },
    });
};
</script>

<template>
    <Head title="Métodos y Tarjetas" />

    <div class="p-6 space-y-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <CreditCard class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    Catálogo de Métodos de Pago y Tarjetas
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Administra las opciones de pago por transferencia, efectivo o tarjeta de débito/crédito y las cuentas bancarias de la empresa.
                </p>
            </div>
        </div>

        <!-- Tabs Nav -->
        <div class="flex gap-2 border-b border-zinc-200 dark:border-zinc-800">
            <button
                @click="activeTab = 'cards'"
                :class="[
                    'px-4 py-2.5 text-sm font-semibold border-b-2 transition flex items-center gap-2',
                    activeTab === 'cards'
                        ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400'
                ]"
            >
                <Landmark class="h-4 w-4" /> Tarjetas y Cuentas Corporativas
            </button>
            <button
                @click="activeTab = 'methods'"
                :class="[
                    'px-4 py-2.5 text-sm font-semibold border-b-2 transition flex items-center gap-2',
                    activeTab === 'methods'
                        ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400'
                ]"
            >
                <Wallet class="h-4 w-4" /> Métodos de Pago Base
            </button>
            <button
                @click="activeTab = 'types'"
                :class="[
                    'px-4 py-2.5 text-sm font-semibold border-b-2 transition flex items-center gap-2',
                    activeTab === 'types'
                        ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400'
                ]"
            >
                <CreditCard class="h-4 w-4" /> Tipos de Tarjeta (Visa, MC, etc.)
            </button>
        </div>

        <!-- TAB 1: PAYMENT CARDS / ACCOUNTS -->
        <div v-if="activeTab === 'cards'" class="space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Cuentas y Tarjetas Registradas</h2>
                <Button @click="openCreateCard" class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="h-4 w-4 mr-1.5" /> Agregar Cuenta/Tarjeta
                </Button>
            </div>

            <DataTable :columns="cardColumns" :data="paymentCardRows" searchPlaceholder="Buscar cuenta por alias, banco...">
                <template #cell-alias="{ row }"><span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ row.alias }}</span></template>
                <template #cell-payment_method_name="{ row }"><Badge variant="outline">{{ row.payment_method_name }}</Badge></template>
                <template #cell-card_number_masked="{ row }"><span class="font-mono text-zinc-500">{{ row.card_number_masked || '-' }}</span></template>
                <template #cell-actions="{ row }">
                    <ActionsDropdown :actions="[{ label: 'Editar', icon: 'edit', onClick: () => openEditCard(row) }, { label: 'Eliminar', icon: 'delete', variant: 'destructive', onClick: () => openDelete('card', row) }]" />
                </template>
            </DataTable>
        </div>

        <!-- TAB 2: PAYMENT METHODS -->
        <div v-if="activeTab === 'methods'" class="space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Catálogo de Métodos de Pago Base</h2>
                <Button @click="openCreateMethod" class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="h-4 w-4 mr-1.5" /> Agregar Método
                </Button>
            </div>

            <DataTable :columns="methodColumns" :data="paymentMethods" searchPlaceholder="Buscar método de pago...">
                <template #cell-name="{ row }"><span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ row.name }}</span></template>
                <template #cell-slug="{ row }"><span class="font-mono text-zinc-500">{{ row.slug }}</span></template>
                <template #cell-requires_card_details="{ row }">
                    <Badge :variant="row.requires_card_details ? 'default' : 'secondary'">{{ row.requires_card_details ? 'Sí' : 'No' }}</Badge>
                </template>
                <template #cell-actions="{ row }">
                    <ActionsDropdown :actions="[{ label: 'Editar', icon: 'edit', onClick: () => openEditMethod(row) }, { label: 'Eliminar', icon: 'delete', variant: 'destructive', onClick: () => openDelete('method', row) }]" />
                </template>
            </DataTable>
        </div>

        <!-- TAB 3: CARD TYPES -->
        <div v-if="activeTab === 'types'" class="space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Catálogo de Tipos de Tarjetas</h2>
                <Button @click="openCreateCardType" class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Plus class="h-4 w-4 mr-1.5" /> Agregar Tipo
                </Button>
            </div>

            <DataTable :columns="cardTypeColumns" :data="cardTypes" searchPlaceholder="Buscar tipo de tarjeta...">
                <template #cell-name="{ row }"><span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ row.name }}</span></template>
                <template #cell-is_active="{ row }"><Badge :variant="row.is_active ? 'default' : 'secondary'">{{ row.is_active ? 'Activo' : 'Inactivo' }}</Badge></template>
                <template #cell-actions="{ row }">
                    <ActionsDropdown :actions="[{ label: 'Editar', icon: 'edit', onClick: () => openEditCardType(row) }, { label: 'Eliminar', icon: 'delete', variant: 'destructive', onClick: () => openDelete('type', row) }]" />
                </template>
            </DataTable>
        </div>
    </div>

    <!-- MODAL PAYMENT METHOD -->
    <Dialog :open="showMethodModal" @update:open="showMethodModal = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader><DialogTitle>{{ isEditing ? 'Editar Método' : 'Nuevo Método de Pago' }}</DialogTitle></DialogHeader>
            <form @submit.prevent="submitMethod" class="space-y-4 py-2">
                <div class="space-y-1">
                    <Label>Nombre del Método</Label>
                    <Input v-model="methodForm.name" placeholder="Ej: Tarjeta de Débito" required />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="req_card" v-model="methodForm.requires_card_details" class="rounded border-zinc-300 text-indigo-600" />
                    <Label for="req_card">Requiere especificar datos de tarjeta / cuenta</Label>
                </div>
                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showMethodModal = false">Cancelar</Button>
                    <Button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700">Guardar</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- MODAL CARD TYPE -->
    <Dialog :open="showCardTypeModal" @update:open="showCardTypeModal = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader><DialogTitle>{{ isEditing ? 'Editar Tipo de Tarjeta' : 'Nuevo Tipo de Tarjeta' }}</DialogTitle></DialogHeader>
            <form @submit.prevent="submitCardType" class="space-y-4 py-2">
                <div class="space-y-1">
                    <Label>Nombre (Visa, Mastercard, etc.)</Label>
                    <Input v-model="cardTypeForm.name" placeholder="Ej: Visa" required />
                </div>
                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showCardTypeModal = false">Cancelar</Button>
                    <Button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700">Guardar</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- MODAL PAYMENT CARD / ACCOUNT -->
    <Dialog :open="showCardModal" @update:open="showCardModal = $event">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader><DialogTitle>{{ isEditing ? 'Editar Cuenta/Tarjeta' : 'Nueva Cuenta/Tarjeta' }}</DialogTitle></DialogHeader>
            <form @submit.prevent="submitCard" class="space-y-4 py-2">
                <div class="space-y-1">
                    <Label>Alias de la Cuenta/Tarjeta</Label>
                    <Input v-model="cardForm.alias" placeholder="Ej: BBVA Débito Operaciones" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label>Método de Pago</Label>
                        <select v-model="cardForm.payment_method_id" class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 text-sm" required>
                            <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.id">{{ pm.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <Label>Tipo de Tarjeta (opcional)</Label>
                        <select v-model="cardForm.card_type_id" class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 text-sm">
                            <option value="">Ninguno</option>
                            <option v-for="ct in cardTypes" :key="ct.id" :value="ct.id">{{ ct.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label>Banco Emisor</Label>
                        <Input v-model="cardForm.bank_name" placeholder="Ej: BBVA / Banamex" />
                    </div>
                    <div class="space-y-1">
                        <Label>Número Máscara / Cuenta</Label>
                        <Input v-model="cardForm.card_number_masked" placeholder="Ej: **** 4321" />
                    </div>
                </div>
                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="showCardModal = false">Cancelar</Button>
                    <Button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700">Guardar</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
        :open="showDeleteDialog"
        @update:open="showDeleteDialog = $event"
        title="¿Eliminar Registro?"
        description="¿Deseas enviar este elemento a la papelera (SoftDelete)?"
        @confirm="confirmDelete"
    />
</template>
