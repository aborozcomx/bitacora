<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { AlertTriangle } from '@lucide/vue';

defineProps<{
    open: boolean;
    title?: string;
    description?: string;
    loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'confirm']);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="flex flex-row items-start gap-4 space-y-0">
                <div class="p-3 rounded-full bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400 shrink-0">
                    <AlertTriangle class="h-6 w-6" />
                </div>
                <div class="space-y-1">
                    <DialogTitle class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ title || '¿Confirmar eliminación?' }}
                    </DialogTitle>
                    <DialogDescription class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ description || 'Esta acción enviará el registro a la papelera (SoftDelete) y podrá ser recuperado por un administrador.' }}
                    </DialogDescription>
                </div>
            </DialogHeader>
            <DialogFooter class="flex flex-col-reverse sm:flex-row gap-2 mt-4">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="loading"
                    @click="emit('update:open', false)"
                >
                    Cancelar
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="loading"
                    @click="emit('confirm')"
                >
                    <span v-if="loading">Eliminando...</span>
                    <span v-else>Sí, eliminar</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
