<script setup lang="ts">
import { ref, computed } from 'vue';
import { Calendar as CalendarIcon, ChevronLeft, ChevronRight, Check } from '@lucide/vue';
import { Button } from '@/components/ui/button';

const props = withDefaults(defineProps<{
    modelValue?: string;
    placeholder?: string;
    required?: boolean;
}>(), {
    modelValue: '',
    placeholder: 'Seleccionar fecha',
    required: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const isOpen = ref(false);

// Current view month & year
const initialDate = props.modelValue ? new Date(props.modelValue + 'T00:00:00') : new Date();
const viewYear = ref(initialDate.getFullYear());
const viewMonth = ref(initialDate.getMonth()); // 0-indexed

const monthNames = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const dayNames = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'];

const selectedDateFormatted = computed(() => {
    if (!props.modelValue) return '';
    const d = new Date(props.modelValue + 'T00:00:00');
    if (isNaN(d.getTime())) return props.modelValue;
    return d.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
});

const prevMonth = () => {
    if (viewMonth.value === 0) {
        viewMonth.value = 11;
        viewYear.value--;
    } else {
        viewMonth.value--;
    }
};

const nextMonth = () => {
    if (viewMonth.value === 11) {
        viewMonth.value = 0;
        viewYear.value++;
    } else {
        viewMonth.value++;
    }
};

// Calendar days calculation for current view
const calendarDays = computed(() => {
    const days: Array<{ dateStr: string; dayNumber: number; isCurrentMonth: boolean; isToday: boolean; isSelected: boolean }> = [];
    
    // First day of current month
    const firstDay = new Date(viewYear.value, viewMonth.value, 1);
    // Day of week (0=Sunday, 1=Monday, etc.) -> Adjust to Monday=0
    let startDayOfWeek = firstDay.getDay() - 1;
    if (startDayOfWeek === -1) startDayOfWeek = 6;

    // Total days in current month
    const lastDay = new Date(viewYear.value, viewMonth.value + 1, 0);
    const totalDays = lastDay.getDate();

    // Previous month padding days
    const prevMonthLastDay = new Date(viewYear.value, viewMonth.value, 0).getDate();
    for (let i = startDayOfWeek - 1; i >= 0; i--) {
        const dayNum = prevMonthLastDay - i;
        const month = viewMonth.value === 0 ? 12 : viewMonth.value;
        const year = viewMonth.value === 0 ? viewYear.value - 1 : viewYear.value;
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
        days.push({ dateStr, dayNumber: dayNum, isCurrentMonth: false, isToday: false, isSelected: false });
    }

    // Current month days
    const todayStr = new Date().toISOString().substring(0, 10);
    for (let dayNum = 1; dayNum <= totalDays; dayNum++) {
        const month = viewMonth.value + 1;
        const dateStr = `${viewYear.value}-${String(month).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
        const isToday = dateStr === todayStr;
        const isSelected = dateStr === props.modelValue;
        days.push({ dateStr, dayNumber: dayNum, isCurrentMonth: true, isToday, isSelected });
    }

    // Next month padding days to complete 42 cells (6 rows of 7)
    const remainingCells = 42 - days.length;
    for (let dayNum = 1; dayNum <= remainingCells; dayNum++) {
        const month = viewMonth.value === 11 ? 1 : viewMonth.value + 2;
        const year = viewMonth.value === 11 ? viewYear.value + 1 : viewYear.value;
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
        days.push({ dateStr, dayNumber: dayNum, isCurrentMonth: false, isToday: false, isSelected: false });
    }

    return days;
});

const selectDay = (dateStr: string) => {
    emit('update:modelValue', dateStr);
    isOpen.value = false;
};

const selectToday = () => {
    const todayStr = new Date().toISOString().substring(0, 10);
    const today = new Date();
    viewYear.value = today.getFullYear();
    viewMonth.value = today.getMonth();
    emit('update:modelValue', todayStr);
    isOpen.value = false;
};
</script>

<template>
    <div class="relative w-full">
        <!-- Input Button Trigger -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="w-full h-9 rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1 text-sm text-left flex items-center justify-between shadow-xs hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
        >
            <span class="flex items-center gap-2" :class="props.modelValue ? 'text-zinc-900 dark:text-zinc-100 font-medium' : 'text-zinc-400'">
                <CalendarIcon class="h-4 w-4 text-indigo-600 dark:text-indigo-400 shrink-0" />
                {{ selectedDateFormatted || placeholder }}
            </span>
            <span class="text-xs text-zinc-400 font-mono">{{ props.modelValue }}</span>
        </button>

        <!-- Shadcn Popover Calendar Grid -->
        <div
            v-if="isOpen"
            class="absolute left-0 mt-2 z-50 w-72 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl p-4 space-y-3 animate-in fade-in-50 zoom-in-95"
        >
            <!-- Calendar Header: Month & Year + Controls -->
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                    {{ monthNames[viewMonth] }} {{ viewYear }}
                </span>
                <div class="flex items-center gap-1">
                    <Button type="button" variant="outline" size="icon" class="h-7 w-7" @click="prevMonth">
                        <ChevronLeft class="h-4 w-4" />
                    </Button>
                    <Button type="button" variant="outline" size="icon" class="h-7 w-7" @click="nextMonth">
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Days of Week Header -->
            <div class="grid grid-cols-7 text-center text-xs font-semibold text-zinc-400">
                <div v-for="d in dayNames" :key="d" class="py-1">{{ d }}</div>
            </div>

            <!-- Days Grid -->
            <div class="grid grid-cols-7 gap-1 text-center text-xs">
                <button
                    v-for="(cell, i) in calendarDays"
                    :key="i"
                    type="button"
                    @click="selectDay(cell.dateStr)"
                    :class="[
                        'h-8 w-8 rounded-lg flex items-center justify-center font-medium transition mx-auto',
                        !cell.isCurrentMonth ? 'text-zinc-300 dark:text-zinc-600' : 'text-zinc-700 dark:text-zinc-200',
                        cell.isToday && !cell.isSelected ? 'border border-indigo-500 font-bold text-indigo-600' : '',
                        cell.isSelected ? 'bg-indigo-600 text-white font-bold shadow-md' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800'
                    ]"
                >
                    {{ cell.dayNumber }}
                </button>
            </div>

            <!-- Footer: Quick Today Action -->
            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center text-xs">
                <Button type="button" variant="ghost" size="sm" class="h-7 text-indigo-600 dark:text-indigo-400 font-semibold" @click="selectToday">
                    Seleccionar Hoy
                </Button>
                <Button type="button" variant="ghost" size="sm" class="h-7 text-zinc-400" @click="isOpen = false">
                    Cerrar
                </Button>
            </div>
        </div>
    </div>
</template>
