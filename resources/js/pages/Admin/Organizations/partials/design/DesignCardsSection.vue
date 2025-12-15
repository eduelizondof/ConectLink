<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Save, Loader2, Sparkles } from 'lucide-vue-next';
import { ColorPicker } from '@/components/ui/color-picker';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
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
    card_style: 'solid',
    card_background_color: '#ffffff',
    card_border_radius: 'lg',
    card_shadow: true,
    card_border_color: '',
    card_glow_enabled: false,
    card_glow_color: '#3b82f6',
    card_glow_color_secondary: '#8b5cf6',
    card_glow_variant: 'primary',
});

// Load settings when profile changes
watch(() => props.profile, (profile) => {
    if (profile?.settings) {
        const s = profile.settings;
        form.card_style = s.card_style || 'solid';
        form.card_background_color = s.card_background_color || '#ffffff';
        form.card_border_radius = s.card_border_radius || 'lg';
        form.card_shadow = s.card_shadow ?? true;
        form.card_border_color = s.card_border_color || '';
        form.card_glow_enabled = s.card_glow_enabled ?? false;
        form.card_glow_color = s.card_glow_color || '#3b82f6';
        form.card_glow_color_secondary = s.card_glow_color_secondary || '#8b5cf6';
        form.card_glow_variant = s.card_glow_variant || 'primary';
    }
}, { immediate: true });

// Options
const cardStyles = [
    { value: 'solid', label: 'Sólido' },
    { value: 'transparent', label: 'Transparente' },
    { value: 'glass', label: 'Cristal' },
];

const borderRadiusOptions = [
    { value: 'none', label: 'Sin bordes' },
    { value: 'sm', label: 'Pequeño' },
    { value: 'md', label: 'Mediano' },
    { value: 'lg', label: 'Grande' },
    { value: 'xl', label: 'Extra grande' },
    { value: '2xl', label: 'Redondeado' },
    { value: 'full', label: 'Completo' },
];

const glowVariants = [
    { value: 'primary', label: 'Primario' },
    { value: 'cyan', label: 'Cian' },
    { value: 'purple', label: 'Púrpura' },
    { value: 'rainbow', label: 'Arcoíris' },
    { value: 'default', label: 'Personalizado' },
];

function save() {
    if (!props.profile) return;

    form.put(`/admin/profiles/${props.profile.id}/design/cards`, {
        preserveScroll: true,
        onSuccess: () => {
            swal.success('¡Tarjetas guardadas!');
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
            <Label>Estilo de tarjeta</Label>
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="style in cardStyles"
                    :key="style.value"
                    @click="form.card_style = style.value"
                    class="rounded-lg border px-3 py-2 text-xs transition-colors"
                    :class="form.card_style === style.value
                        ? 'border-primary bg-primary/5 text-primary'
                        : 'hover:bg-muted'"
                >
                    {{ style.label }}
                </button>
            </div>
        </div>

        <ColorPicker
            v-model="form.card_background_color"
            label="Color de tarjeta"
        />

        <div class="space-y-2">
            <Label>Bordes redondeados</Label>
            <Select v-model="form.card_border_radius">
                <SelectTrigger>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="opt in borderRadiusOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="flex items-center justify-between">
            <Label>Sombra</Label>
            <Switch
                :checked="form.card_shadow"
                @update:checked="form.card_shadow = $event"
            />
        </div>

        <!-- Glow Effect -->
        <div class="space-y-4 rounded-lg border bg-muted/50 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Sparkles class="h-4 w-4 text-amber-500" />
                    <Label>Efecto de brillo</Label>
                </div>
                <Switch
                    :checked="form.card_glow_enabled"
                    @update:checked="form.card_glow_enabled = $event"
                />
            </div>

            <div v-if="form.card_glow_enabled" class="space-y-4">
                <div class="space-y-2">
                    <Label>Variante</Label>
                    <Select v-model="form.card_glow_variant">
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="variant in glowVariants" :key="variant.value" :value="variant.value">
                                {{ variant.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div v-if="form.card_glow_variant === 'default'" class="grid gap-4 sm:grid-cols-2">
                    <ColorPicker
                        v-model="form.card_glow_color"
                        label="Color 1"
                    />
                    <ColorPicker
                        v-model="form.card_glow_color_secondary"
                        label="Color 2"
                    />
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-2">
            <Button @click="save" :disabled="form.processing" size="sm" class="gap-1.5">
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <Save v-else class="h-4 w-4" />
                Guardar Tarjetas
            </Button>
        </div>
    </div>
</template>

