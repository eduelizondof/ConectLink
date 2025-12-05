<script setup lang="ts">
import { ref, watch } from 'vue';
import { Pipette } from 'lucide-vue-next';

const props = defineProps<{
    modelValue: string;
    label?: string;
    presets?: string[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const localValue = ref(props.modelValue);

const defaultPresets = [
    '#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b',
    '#ef4444', '#ec4899', '#6366f1', '#14b8a6', '#f97316',
    '#1f2937', '#374151', '#6b7280', '#ffffff', '#000000',
];

const presetColors = props.presets || defaultPresets;

watch(localValue, (val) => {
    emit('update:modelValue', val);
});

watch(() => props.modelValue, (val) => {
    localValue.value = val;
});

function selectPreset(color: string) {
    localValue.value = color;
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


