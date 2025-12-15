<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Image, Square, Palette, Save, Loader2 } from 'lucide-vue-next';
import { ImageUpload } from '@/components/ui/image-upload';
import { ColorPicker } from '@/components/ui/color-picker';
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
    background_type: 'solid',
    background_color: '#ffffff',
    background_gradient_start: '#3b82f6',
    background_gradient_end: '#8b5cf6',
    background_gradient_direction: 'to-b',
    background_image: null as File | null,
    background_overlay_opacity: 0,
    background_pattern: 'none',
    background_pattern_opacity: 10,
});

// Load settings when profile changes
watch(() => props.profile, (profile) => {
    if (profile?.settings) {
        const s = profile.settings;
        form.background_type = s.background_type || 'solid';
        form.background_color = s.background_color || '#ffffff';
        form.background_gradient_start = s.background_gradient_start || '#3b82f6';
        form.background_gradient_end = s.background_gradient_end || '#8b5cf6';
        form.background_gradient_direction = s.background_gradient_direction || 'to-b';
        form.background_overlay_opacity = s.background_overlay_opacity || 0;
        form.background_pattern = s.background_pattern || 'none';
        form.background_pattern_opacity = s.background_pattern_opacity || 10;
        form.background_image = null;
    }
}, { immediate: true });

// Options
const backgroundTypes = [
    { value: 'solid', label: 'Color sólido', icon: Square },
    { value: 'gradient', label: 'Gradiente', icon: Palette },
    { value: 'image', label: 'Imagen', icon: Image },
];

const gradientDirections = [
    { value: 'to-b', label: 'Vertical ↓' },
    { value: 'to-r', label: 'Horizontal →' },
    { value: 'to-br', label: 'Diagonal ↘' },
    { value: 'to-bl', label: 'Diagonal ↙' },
];

const backgroundPatterns = [
    { value: 'none', label: 'Ninguno' },
    { value: 'dots', label: 'Puntos' },
    { value: 'grid', label: 'Cuadrícula' },
    { value: 'waves', label: 'Ondas' },
    { value: 'noise', label: 'Ruido' },
];

function save() {
    if (!props.profile) return;

    // Debug: Log form values
    console.log('Form values:', {
        background_type: form.background_type,
        background_color: form.background_color,
        background_gradient_start: form.background_gradient_start,
        background_gradient_end: form.background_gradient_end,
        background_gradient_direction: form.background_gradient_direction,
        background_pattern: form.background_pattern,
        background_pattern_opacity: form.background_pattern_opacity,
        background_overlay_opacity: form.background_overlay_opacity,
        background_image: form.background_image,
    });

    // Ensure background_type is set
    if (!form.background_type) {
        console.error('background_type is missing!');
        swal.error('Por favor selecciona un tipo de fondo');
        return;
    }

    // Build data object with only necessary fields
    const submitData: Record<string, any> = {
        background_type: form.background_type,
        background_pattern: form.background_pattern || 'none',
        background_pattern_opacity: form.background_pattern_opacity ?? 10,
    };

    console.log('Initial submitData:', submitData);

    // Add fields based on background type
    if (form.background_type === 'solid') {
        if (form.background_color) {
            submitData.background_color = form.background_color;
        }
    } else if (form.background_type === 'gradient') {
        if (form.background_gradient_start) {
            submitData.background_gradient_start = form.background_gradient_start;
        }
        if (form.background_gradient_end) {
            submitData.background_gradient_end = form.background_gradient_end;
        }
        if (form.background_gradient_direction) {
            submitData.background_gradient_direction = form.background_gradient_direction;
        }
    } else if (form.background_type === 'image') {
        if (form.background_overlay_opacity !== undefined) {
            submitData.background_overlay_opacity = form.background_overlay_opacity;
        }
        if (form.background_image) {
            submitData.background_image = form.background_image;
        }
    }

    console.log('Final submitData before form creation:', submitData);
    console.log('submitData.background_type:', submitData.background_type);

    // Create a new form instance with only the necessary data
    const submitForm = useForm(submitData);

    console.log('Form data after creation:', submitForm.data());
    console.log('Form data keys:', Object.keys(submitForm.data()));
    console.log('Has background_image:', !!submitData.background_image);
    
    // Only use forceFormData if there's an image file
    const hasImageFile = submitData.background_image instanceof File;
    
    submitForm.put(`/admin/profiles/${props.profile.id}/design/background`, {
        preserveScroll: true,
        forceFormData: hasImageFile, // Only use FormData when there's an actual file
        onBefore: () => {
            console.log('Before submit - Form data:', submitForm.data());
            console.log('Before submit - Using FormData:', hasImageFile);
        },
        onSuccess: () => {
            swal.success('¡Fondo guardado!');
            emit('updated');
            // Reset background_image after successful save
            form.background_image = null;
        },
        onError: (errors) => {
            console.error('Error saving background:', errors);
            console.error('Submitted data:', submitData);
            console.error('Form data at error:', submitForm.data());
            console.error('Form processing:', submitForm.processing);
            console.error('Using FormData:', hasImageFile);
            swal.error('Error al guardar. Verifica los datos e intenta de nuevo.');
        },
    });
}

// Expose form data for preview
defineExpose({
    formData: computed(() => form.data()),
});
</script>

<template>
    <div class="space-y-4">
        <!-- Background Type -->
        <div class="space-y-2">
            <Label>Tipo de fondo</Label>
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="type in backgroundTypes"
                    :key="type.value"
                    @click="form.background_type = type.value"
                    class="flex flex-col items-center gap-1 rounded-lg border p-3 text-xs transition-colors"
                    :class="form.background_type === type.value
                        ? 'border-primary bg-primary/5 text-primary'
                        : 'hover:bg-muted'"
                >
                    <component :is="type.icon" class="h-4 w-4" />
                    {{ type.label }}
                </button>
            </div>
        </div>

        <!-- Solid Color -->
        <div v-if="form.background_type === 'solid'" class="space-y-2">
            <ColorPicker
                v-model="form.background_color"
                label="Color de fondo"
            />
        </div>

        <!-- Gradient Colors -->
        <div v-if="form.background_type === 'gradient'" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <ColorPicker
                    v-model="form.background_gradient_start"
                    label="Color inicial"
                />
                <ColorPicker
                    v-model="form.background_gradient_end"
                    label="Color final"
                />
            </div>
            <div class="space-y-2">
                <Label>Dirección</Label>
                <div class="grid grid-cols-4 gap-2">
                    <button
                        v-for="dir in gradientDirections"
                        :key="dir.value"
                        @click="form.background_gradient_direction = dir.value"
                        class="rounded-lg border px-2 py-1.5 text-xs transition-colors"
                        :class="form.background_gradient_direction === dir.value
                            ? 'border-primary bg-primary/5 text-primary'
                            : 'hover:bg-muted'"
                    >
                        {{ dir.label }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Background Image -->
        <div v-if="form.background_type === 'image'" class="space-y-4">
            <ImageUpload
                v-model="form.background_image"
                :current-image="profile?.settings?.background_image"
                label="Imagen de fondo"
                aspect-ratio="auto"
                :max-size="4"
            />
            <div class="space-y-2">
                <Label>Opacidad del overlay: {{ form.background_overlay_opacity }}%</Label>
                <Slider
                    :model-value="[form.background_overlay_opacity]"
                    @update:model-value="(val) => form.background_overlay_opacity = val?.[0] ?? 0"
                    :max="100"
                    :step="5"
                />
            </div>
        </div>

        <!-- Background Pattern -->
        <div class="space-y-2">
            <Label>Patrón de fondo</Label>
            <Select v-model="form.background_pattern">
                <SelectTrigger>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="pattern in backgroundPatterns" :key="pattern.value" :value="pattern.value">
                        {{ pattern.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div v-if="form.background_pattern !== 'none'" class="space-y-2">
            <Label>Opacidad del patrón: {{ form.background_pattern_opacity }}%</Label>
            <Slider
                :model-value="[form.background_pattern_opacity]"
                @update:model-value="(val) => form.background_pattern_opacity = val?.[0] ?? 10"
                :max="50"
                :step="5"
            />
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-2">
            <Button @click="save" :disabled="form.processing" size="sm" class="gap-1.5">
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <Save v-else class="h-4 w-4" />
                Guardar Fondo
            </Button>
        </div>
    </div>
</template>

