<script setup lang="ts">
import { ref, watch } from 'vue';
import { Pipette } from 'lucide-vue-next';

const props = defineProps<{
    modelValue: string | null | undefined;
    label?: string;
    presets?: string[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string | null];
}>();

// Use a default color for the color input when value is null/empty
const DEFAULT_COLOR = '#000000';
const getColorValue = (val: string | null | undefined) => {
    return val && val.trim() ? val : DEFAULT_COLOR;
};

const localValue = ref(getColorValue(props.modelValue));
const wasOriginallyEmpty = ref(!props.modelValue || props.modelValue.trim() === '');

const defaultPresets = [
    '#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b',
    '#ef4444', '#ec4899', '#6366f1', '#14b8a6', '#f97316',
    '#1f2937', '#374151', '#6b7280', '#ffffff', '#000000',
];

const presetColors = props.presets || defaultPresets;

watch(localValue, (val) => {
    // If value is empty or default and it was originally empty, emit null
    if ((!val || val.trim() === '' || val === DEFAULT_COLOR) && wasOriginallyEmpty.value) {
        emit('update:modelValue', null);
    } else {
        // Otherwise emit the actual value (or null if empty)
        emit('update:modelValue', val && val.trim() ? val : null);
    }
});

watch(() => props.modelValue, (val) => {
    wasOriginallyEmpty.value = !val || val.trim() === '';
    localValue.value = getColorValue(val);
});

function selectPreset(color: string) {
    localValue.value = color;
}

function handleHexBlur() {
    // Validate hex color format
    const value = localValue.value;
    if (value && !value.match(/^#[0-9A-Fa-f]{6}$/)) {
        // Reset to default if invalid
        localValue.value = DEFAULT_COLOR;
    }
}
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" class="text-sm font-medium">{{ label }}</label>

        <div class="flex items-center gap-3">
            <!-- Color Input -->
            <div class="relative">
                <input
                    type="color"
                    v-model="localValue"
                    class="h-12 w-12 cursor-pointer rounded-lg border-2 border-border p-1"
                />
            </div>

            <!-- Hex Input -->
            <input
                type="text"
                v-model="localValue"
                maxlength="7"
                class="h-12 w-24 rounded-lg border bg-background px-3 text-sm font-mono uppercase"
                placeholder="#000000"
                @blur="handleHexBlur"
            />
        </div>

        <!-- Presets -->
        <div class="flex flex-wrap gap-2 pt-2">
            <button
                v-for="color in presetColors"
                :key="color"
                type="button"
                class="h-8 w-8 rounded-lg border-2 transition-all hover:scale-110"
                :class="localValue === color ? 'border-primary ring-2 ring-primary/20' : 'border-transparent'"
                :style="{ backgroundColor: color }"
                @click="selectPreset(color)"
            />
        </div>
    </div>
</template>





