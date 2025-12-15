<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Save, Loader2 } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Slider } from '@/components/ui/slider';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Profile {
    id: number;
    settings?: Record<string, any>;
}

const props = defineProps<{
    profile: Profile | null;
}>();

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

const swal = useSwal();

const form = useForm({
    animation_entrance: 'fade',
    animation_hover: 'lift',
    animation_delay: 100,
});

// Load settings when profile changes
watch(() => props.profile, (profile) => {
    if (profile?.settings) {
        const s = profile.settings;
        form.animation_entrance = s.animation_entrance || 'fade';
        form.animation_hover = s.animation_hover || 'lift';
        form.animation_delay = s.animation_delay || 100;
    }
}, { immediate: true });

// Options
const entranceAnimations = [
    { value: 'none', label: 'Ninguna' },
    { value: 'fade', label: 'Desvanecer' },
    { value: 'slide-up', label: 'Deslizar arriba' },
    { value: 'slide-down', label: 'Deslizar abajo' },
    { value: 'scale', label: 'Escalar' },
    { value: 'bounce', label: 'Rebotar' },
];

const hoverAnimations = [
    { value: 'none', label: 'Ninguna' },
    { value: 'lift', label: 'Elevar' },
    { value: 'glow', label: 'Brillar' },
    { value: 'pulse', label: 'Pulsar' },
    { value: 'shake', label: 'Sacudir' },
];

function save() {
    if (!props.profile) return;

    form.put(`/admin/profiles/${props.profile.id}/design/animations`, {
        preserveScroll: true,
        onSuccess: () => {
            swal.success('¡Animaciones guardadas!');
            emit('updated');
        },
        onError: () => swal.error('Error al guardar'),
    });
}

// Expose form data for preview
defineExpose({
    formData: computed(() => form.data()),
});
</script>

<template>
    <div class="space-y-4">
        <div class="space-y-2">
            <Label>Animación de entrada</Label>
            <Select v-model="form.animation_entrance">
                <SelectTrigger>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="anim in entranceAnimations" :key="anim.value" :value="anim.value">
                        {{ anim.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="space-y-2">
            <Label>Animación al pasar el cursor</Label>
            <Select v-model="form.animation_hover">
                <SelectTrigger>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="anim in hoverAnimations" :key="anim.value" :value="anim.value">
                        {{ anim.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="space-y-2">
            <Label>Retraso entre elementos: {{ form.animation_delay }}ms</Label>
            <Slider
                :model-value="[form.animation_delay]"
                @update:model-value="form.animation_delay = $event[0]"
                :min="0"
                :max="500"
                :step="50"
            />
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-2">
            <Button @click="save" :disabled="form.processing" size="sm" class="gap-1.5">
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <Save v-else class="h-4 w-4" />
                Guardar Animaciones
            </Button>
        </div>
    </div>
</template>

