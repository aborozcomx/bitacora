import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

let lastSuccessMessage = '';
let lastErrorMessage = '';
let lastWarningMessage = '';
let lastInfoMessage = '';

export function useFlashNotifications() {
    const page = usePage();

    watch(
        () => page.props.flash,
        (flash: any) => {
            if (!flash) return;

            if (flash.success && flash.success !== lastSuccessMessage) {
                lastSuccessMessage = flash.success;
                toast.success('¡Operación Exitosa!', {
                    id: `flash-success-${flash.success}`,
                    description: flash.success,
                    duration: 4000,
                });
                setTimeout(() => {
                    if (lastSuccessMessage === flash.success) {
                        lastSuccessMessage = '';
                    }
                }, 4000);
            }

            if (flash.error && flash.error !== lastErrorMessage) {
                lastErrorMessage = flash.error;
                toast.error('Error en la Operación', {
                    id: `flash-error-${flash.error}`,
                    description: flash.error,
                    duration: 5000,
                });
                setTimeout(() => {
                    if (lastErrorMessage === flash.error) {
                        lastErrorMessage = '';
                    }
                }, 5000);
            }

            if (flash.warning && flash.warning !== lastWarningMessage) {
                lastWarningMessage = flash.warning;
                toast.warning('Advertencia', {
                    id: `flash-warning-${flash.warning}`,
                    description: flash.warning,
                    duration: 4000,
                });
                setTimeout(() => {
                    if (lastWarningMessage === flash.warning) {
                        lastWarningMessage = '';
                    }
                }, 4000);
            }

            if (flash.info && flash.info !== lastInfoMessage) {
                lastInfoMessage = flash.info;
                toast.info('Información', {
                    id: `flash-info-${flash.info}`,
                    description: flash.info,
                    duration: 4000,
                });
                setTimeout(() => {
                    if (lastInfoMessage === flash.info) {
                        lastInfoMessage = '';
                    }
                }, 4000);
            }
        },
        { immediate: true, deep: true }
    );
}
