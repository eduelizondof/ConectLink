<script setup lang="ts">
import { computed, watch, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Save, Loader2, Sparkles, Info } from 'lucide-vue-next';
import { ColorPicker } from '@/components/ui/color-picker';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Slider } from '@/components/ui/slider';

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
    card_glow_duration: 6,
    card_glow_opacity: 1.0,
});

// Load settings when profile changes
watch(() => props.profile, (profile) => {
    if (profile?.settings) {
        const s = profile.settings;
        
        // DEBUG: Log raw settings data
        console.log('🔍 [DesignCardsSection] Loading settings from profile:', {
            raw_settings: s,
            card_shadow_raw: s.card_shadow,
            card_shadow_type: typeof s.card_shadow,
            card_shadow_value: s.card_shadow,
            card_glow_enabled_raw: s.card_glow_enabled,
            card_glow_enabled_type: typeof s.card_glow_enabled,
            card_glow_enabled_value: s.card_glow_enabled,
            card_glow_duration_raw: s.card_glow_duration,
            card_glow_duration_type: typeof s.card_glow_duration,
            card_glow_opacity_raw: s.card_glow_opacity,
            card_glow_opacity_type: typeof s.card_glow_opacity,
        });
        
        // Process and set form values - ensure explicit boolean conversion
        // Convert to strict boolean: true/false, not truthy/falsy
        const cardShadowValue = s.card_shadow === true || s.card_shadow === 1 || s.card_shadow === '1' || s.card_shadow === 'true';
        const cardGlowEnabledValue = s.card_glow_enabled === true || s.card_glow_enabled === 1 || s.card_glow_enabled === '1' || s.card_glow_enabled === 'true';
        
        form.card_style = s.card_style || 'solid';
        form.card_background_color = s.card_background_color || '#ffffff';
        form.card_border_radius = s.card_border_radius || 'lg';
        // Ensure boolean values are properly set - use explicit boolean assignment
        form.card_shadow = cardShadowValue;
        form.card_border_color = s.card_border_color || '';
        form.card_glow_enabled = cardGlowEnabledValue;
        form.card_glow_color = s.card_glow_color || '#3b82f6';
        form.card_glow_color_secondary = s.card_glow_color_secondary || '#8b5cf6';
        form.card_glow_variant = s.card_glow_variant || 'primary';
        // Convert to number, handling both number and string types
        form.card_glow_duration = s.card_glow_duration !== undefined ? Number(s.card_glow_duration) : 6;
        form.card_glow_opacity = s.card_glow_opacity !== undefined ? Number(s.card_glow_opacity) : 1.0;
        
        // DEBUG: Log processed form values
        console.log('✅ [DesignCardsSection] Form values after processing:', {
            card_shadow: form.card_shadow,
            card_shadow_type: typeof form.card_shadow,
            card_shadow_value: form.card_shadow,
            card_glow_enabled: form.card_glow_enabled,
            card_glow_enabled_type: typeof form.card_glow_enabled,
            card_glow_enabled_value: form.card_glow_enabled,
            card_glow_duration: form.card_glow_duration,
            card_glow_duration_type: typeof form.card_glow_duration,
            card_glow_opacity: form.card_glow_opacity,
            card_glow_opacity_type: typeof form.card_glow_opacity,
            form_data: form.data(),
        });
        
        // Force reactivity by using nextTick to ensure DOM is updated
        nextTick(() => {
            console.log('🔄 [DesignCardsSection] Form values after nextTick:', {
                card_shadow: form.card_shadow,
                card_shadow_type: typeof form.card_shadow,
                card_glow_enabled: form.card_glow_enabled,
                card_glow_enabled_type: typeof form.card_glow_enabled,
            });
        });
    } else {
        console.log('⚠️ [DesignCardsSection] No settings found in profile:', profile);
    }
}, { immediate: true, flush: 'post' });

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

    // Ensure boolean values are properly sent as actual booleans
    // Ensure numeric values are properly converted
    const data: Record<string, any> = {
        card_style: form.card_style,
        card_background_color: form.card_background_color,
        card_border_radius: form.card_border_radius,
        card_shadow: !!form.card_shadow, // Convert to explicit boolean
        card_border_color: form.card_border_color || '',
        card_glow_enabled: !!form.card_glow_enabled, // Convert to explicit boolean - ensures false is sent
        card_glow_color: form.card_glow_color,
        card_glow_color_secondary: form.card_glow_color_secondary,
        card_glow_variant: form.card_glow_variant,
        card_glow_duration: Number(form.card_glow_duration) || 6,
        card_glow_opacity: Number(form.card_glow_opacity) || 1.0,
    };

    form.transform(() => data).put(`/admin/profiles/${props.profile.id}/design/cards`, {
        preserveScroll: true,
        onSuccess: () => {
            swal.success('¡Tarjetas guardadas!');
            emit('updated');
        },
        onError: () => swal.error('Error al guardar'),
    });
}

// Expose form data for preview - ensure boolean values are properly exposed
defineExpose({
    formData: computed(() => ({
        card_style: form.card_style,
        card_background_color: form.card_background_color,
        card_border_radius: form.card_border_radius,
        card_shadow: form.card_shadow,
        card_border_color: form.card_border_color,
        card_glow_enabled: form.card_glow_enabled,
        card_glow_color: form.card_glow_color,
        card_glow_color_secondary: form.card_glow_color_secondary,
        card_glow_variant: form.card_glow_variant,
        card_glow_duration: form.card_glow_duration,
        card_glow_opacity: form.card_glow_opacity,
    })),
});
</script>

<template>
    <div class="space-y-4">
        <!-- Estilo de tarjeta -->
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <Label>Estilo de tarjeta</Label>
                <div class="group relative">
                    <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help" />
                    <div class="absolute left-0 top-6 z-10 hidden w-64 rounded-md border bg-popover p-2 text-xs text-popover-foreground shadow-md group-hover:block">
                        <p><strong>Sólido:</strong> Fondo completamente opaco, ideal para fondos claros.</p>
                        <p class="mt-1"><strong>Transparente:</strong> Fondo semitransparente, perfecto para fondos con gradientes.</p>
                        <p class="mt-1"><strong>Cristal:</strong> Efecto glassmorphism con blur, moderno y elegante.</p>
                    </div>
                </div>
            </div>
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

        <!-- Color de tarjeta -->
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <Label>Color de tarjeta</Label>
                <div class="group relative">
                    <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help" />
                    <div class="absolute left-0 top-6 z-10 hidden w-64 rounded-md border bg-popover p-2 text-xs text-popover-foreground shadow-md group-hover:block">
                        Color base de las tarjetas. En modo transparente o cristal, se aplicará con opacidad automáticamente.
                    </div>
                </div>
            </div>
            <ColorPicker
                v-model="form.card_background_color"
                label=""
            />
        </div>

        <!-- Bordes redondeados -->
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <Label>Bordes redondeados</Label>
                <div class="group relative">
                    <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help" />
                    <div class="absolute left-0 top-6 z-10 hidden w-64 rounded-md border bg-popover p-2 text-xs text-popover-foreground shadow-md group-hover:block">
                        Controla qué tan redondeados son los bordes de las tarjetas. "Completo" crea bordes muy redondeados.
                    </div>
                </div>
            </div>
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

        <!-- Sombra -->
        <div class="flex items-center justify-between rounded-lg border p-3">
            <div class="flex items-center gap-2">
                <Checkbox
                    v-model="form.card_shadow"
                    id="card_shadow"
                />
                <div class="flex items-center gap-2">
                    <Label for="card_shadow" class="cursor-pointer">Sombra</Label>
                    <div class="group relative">
                        <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help" />
                        <div class="absolute left-0 top-6 z-10 hidden w-64 rounded-md border bg-popover p-2 text-xs text-popover-foreground shadow-md group-hover:block">
                            Agrega una sombra sutil a las tarjetas para darles profundidad. La intensidad varía según el estilo seleccionado.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Glow Effect -->
        <div class="space-y-4 rounded-lg border bg-muted/50 p-4">
            <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Checkbox
                            v-model="form.card_glow_enabled"
                            id="card_glow_enabled"
                        />
                    <div class="flex items-center gap-2">
                        <Sparkles class="h-4 w-4 text-amber-500" />
                        <Label for="card_glow_enabled" class="cursor-pointer">Efecto de brillo</Label>
                        <div class="group relative">
                            <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help" />
                            <div class="absolute left-0 top-6 z-10 hidden w-64 rounded-md border bg-popover p-2 text-xs text-popover-foreground shadow-md group-hover:block">
                                Agrega un borde animado con efecto de brillo alrededor de las tarjetas. Ideal para destacar elementos importantes.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="form.card_glow_enabled" class="space-y-4 pl-6 border-l-2 border-muted">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <Label>Variante</Label>
                        <div class="group relative">
                            <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help" />
                            <div class="absolute left-0 top-6 z-10 hidden w-64 rounded-md border bg-popover p-2 text-xs text-popover-foreground shadow-md group-hover:block">
                                <p><strong>Primario:</strong> Usa los colores primarios del perfil.</p>
                                <p class="mt-1"><strong>Cian/Púrpura/Arcoíris:</strong> Variantes predefinidas con efectos únicos.</p>
                                <p class="mt-1"><strong>Personalizado:</strong> Define tus propios colores para el efecto.</p>
                            </div>
                        </div>
                    </div>
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
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <Label class="text-xs">Color 1</Label>
                            <div class="group relative">
                                <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help" />
                                <div class="absolute left-0 top-6 z-10 hidden w-64 rounded-md border bg-popover p-2 text-xs text-popover-foreground shadow-md group-hover:block">
                                    Primer color del gradiente del efecto de brillo.
                                </div>
                            </div>
                        </div>
                        <ColorPicker
                            v-model="form.card_glow_color"
                            label=""
                        />
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <Label class="text-xs">Color 2</Label>
                            <div class="group relative">
                                <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help" />
                                <div class="absolute left-0 top-6 z-10 hidden w-64 rounded-md border bg-popover p-2 text-xs text-popover-foreground shadow-md group-hover:block">
                                    Segundo color del gradiente del efecto de brillo. Se mezcla con el Color 1 para crear la animación.
                                </div>
                            </div>
                        </div>
                        <ColorPicker
                            v-model="form.card_glow_color_secondary"
                            label=""
                        />
                    </div>
                </div>

                <!-- Glow Duration -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Label class="text-xs">Velocidad de animación</Label>
                            <div class="group relative">
                                <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help" />
                                <div class="absolute left-0 top-6 z-10 hidden w-64 rounded-md border bg-popover p-2 text-xs text-popover-foreground shadow-md group-hover:block">
                                    Controla qué tan rápido gira el efecto de brillo. Valores más bajos = más rápido, valores más altos = más lento.
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-muted-foreground tabular-nums">{{ form.card_glow_duration }}s</span>
                    </div>
                    <Slider
                        :model-value="[form.card_glow_duration]"
                        @update:model-value="(val) => { if (val) form.card_glow_duration = val[0] }"
                        :min="1"
                        :max="30"
                        :step="1"
                        class="w-full"
                    />
                    <div class="flex justify-between text-[10px] text-muted-foreground">
                        <span>Rápido (1s)</span>
                        <span>Lento (30s)</span>
                    </div>
                </div>

                <!-- Glow Opacity -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Label class="text-xs">Intensidad del brillo</Label>
                            <div class="group relative">
                                <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help" />
                                <div class="absolute left-0 top-6 z-10 hidden w-64 rounded-md border bg-popover p-2 text-xs text-popover-foreground shadow-md group-hover:block">
                                    Controla qué tan visible es el efecto de brillo. 0% = invisible, 100% = completamente visible.
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-muted-foreground tabular-nums">{{ Math.round(form.card_glow_opacity * 100) }}%</span>
                    </div>
                    <Slider
                        :model-value="[form.card_glow_opacity]"
                        @update:model-value="(val) => { if (val) form.card_glow_opacity = val[0] }"
                        :min="0"
                        :max="1"
                        :step="0.1"
                        class="w-full"
                    />
                    <div class="flex justify-between text-[10px] text-muted-foreground">
                        <span>Sutil (0%)</span>
                        <span>Intenso (100%)</span>
                    </div>
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

