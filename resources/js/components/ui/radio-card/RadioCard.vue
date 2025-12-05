<script setup lang="ts">
import { computed } from 'vue';
import { Check } from 'lucide-vue-next';

const props = defineProps<{
    value: string | number | boolean;
    modelValue: string | number | boolean;
    label: string;
    description?: string;
    icon?: any;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string | number | boolean];
}>();

const isSelected = computed(() => props.modelValue === props.value);

function select() {
    if (!props.disabled) {
        emit('update:modelValue', props.value);
    }
}
</script>

<template>
    <button
        type="button"
        class="relative flex w-full items-start gap-4 rounded-xl border-2 p-4 text-left transition-all"
        :class="[
            isSelected
                ? 'border-primary bg-primary/5 shadow-sm'
                : 'border-border hover:border-primary/50 hover:bg-muted/50',
            disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
        ]"
        @click="select"
        :disabled="disabled"
    >
        <!-- Icon -->
        <div
            v-if="icon"
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-colors"
            :class="isSelected ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
        >
            <component :is="icon" class="h-6 w-6" />
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            <p class="font-semibold" :class="isSelected ? 'text-primary' : ''">{{ label }}</p>
            <p v-if="description" class="mt-1 text-sm text-muted-foreground">{{ description }}</p>
        </div>

        <!-- Check indicator -->
        <div
            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition-all"
            :class="isSelected
                ? 'border-primary bg-primary text-primary-foreground'
                : 'border-muted-foreground/30'"
        >
            <Check v-if="isSelected" class="h-4 w-4" />
        </div>
    </button>
</template>


