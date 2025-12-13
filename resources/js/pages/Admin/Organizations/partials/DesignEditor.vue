<script setup lang="ts">
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { 
    Palette,
    Image,
    Sparkles,
    Type,
    Square,
    Circle,
    RectangleHorizontal,
    ChevronDown,
    Save,
    RotateCcw,
    Wand2,
    Layers,
} from 'lucide-vue-next';
import { RadioCard } from '@/components/ui/radio-card';
import { ImageUpload } from '@/components/ui/image-upload';
import { ColorPicker } from '@/components/ui/color-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { Slider } from '@/components/ui/slider';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import ProfilePreview from './ProfilePreview.vue';

interface Profile {
    id: number;
    name: string;
    is_primary: boolean;
    settings?: Record<string, any>;
    [key: string]: any;
}

interface Organization {
    id: number;
    name: string;
    profiles: Profile[];
    [key: string]: any;
}

const props = defineProps<{
    organization: Organization;
    selectedProfileId: number | null;
}>();

const emit = defineEmits<{
    (e: 'selectProfile', id: number): void;
}>();

const swal = useSwal();

const selectedProfile = computed(() =>
    props.organization.profiles.find(p => p.id === props.selectedProfileId)
);

// Form for settings
const settingsForm = useForm({
    // Background
    background_type: 'solid',
    background_color: '#ffffff',
    background_gradient_start: '#3b82f6',
    background_gradient_end: '#8b5cf6',
    background_gradient_direction: 'to-b',
    background_image: null as File | null,
    background_overlay_opacity: 0,
    background_pattern: 'none',
    background_pattern_opacity: 10,

    // Colors
    primary_color: '#3b82f6',
    secondary_color: '#8b5cf6',
    text_color: '#1f2937',
    text_secondary_color: '#6b7280',

    // Card
    card_style: 'solid',
    card_background_color: '#ffffff',
    card_border_radius: 'lg',
    card_shadow: true,
    card_border_color: '',
    card_glow_enabled: false,
    card_glow_color: '#3b82f6',
    card_glow_color_secondary: '#8b5cf6',
    card_glow_variant: 'primary',

    // Typography
    font_family: 'Inter',
    font_size: 'base',

    // Animations
    animation_entrance: 'fade',
    animation_hover: 'lift',
    animation_delay: 100,

    // Visual Effects
    show_particles: false,
    particles_style: 'dots',
    particles_color: '',

    // Layout
    layout_style: 'centered',
    show_profile_photo: true,
    photo_style: 'circle',
    photo_size: 'lg',

    // Social
    social_style: 'icons',
    social_size: 'md',
    social_colored: true,
});

// Load settings when profile changes
watch(() => selectedProfile.value, (profile) => {
    if (profile?.settings) {
        const s = profile.settings;
        settingsForm.background_type = s.background_type || 'solid';
        settingsForm.background_color = s.background_color || '#ffffff';
        settingsForm.background_gradient_start = s.background_gradient_start || '#3b82f6';
        settingsForm.background_gradient_end = s.background_gradient_end || '#8b5cf6';
        settingsForm.background_gradient_direction = s.background_gradient_direction || 'to-b';
        settingsForm.background_overlay_opacity = s.background_overlay_opacity || 0;
        settingsForm.background_pattern = s.background_pattern || 'none';
        settingsForm.background_pattern_opacity = s.background_pattern_opacity || 10;

        settingsForm.primary_color = s.primary_color || '#3b82f6';
        settingsForm.secondary_color = s.secondary_color || '#8b5cf6';
        settingsForm.text_color = s.text_color || '#1f2937';
        settingsForm.text_secondary_color = s.text_secondary_color || '#6b7280';

        settingsForm.card_style = s.card_style || 'solid';
        settingsForm.card_background_color = s.card_background_color || '#ffffff';
        settingsForm.card_border_radius = s.card_border_radius || 'lg';
        settingsForm.card_shadow = s.card_shadow ?? true;
        settingsForm.card_border_color = s.card_border_color || '';
        settingsForm.card_glow_enabled = s.card_glow_enabled ?? false;
        settingsForm.card_glow_color = s.card_glow_color || '#3b82f6';
        settingsForm.card_glow_color_secondary = s.card_glow_color_secondary || '#8b5cf6';
        settingsForm.card_glow_variant = s.card_glow_variant || 'primary';

        settingsForm.font_family = s.font_family || 'Inter';
        settingsForm.font_size = s.font_size || 'base';

        settingsForm.animation_entrance = s.animation_entrance || 'fade';
        settingsForm.animation_hover = s.animation_hover || 'lift';
        settingsForm.animation_delay = s.animation_delay || 100;

        settingsForm.show_particles = s.show_particles ?? false;
        settingsForm.particles_style = s.particles_style || 'dots';
        settingsForm.particles_color = s.particles_color || '';

        settingsForm.layout_style = s.layout_style || 'centered';
        settingsForm.show_profile_photo = s.show_profile_photo ?? true;
        settingsForm.photo_style = s.photo_style || 'circle';
        settingsForm.photo_size = s.photo_size || 'lg';

        settingsForm.social_style = s.social_style || 'icons';
        settingsForm.social_size = s.social_size || 'md';
        settingsForm.social_colored = s.social_colored ?? true;
    }
}, { immediate: true });

// Accordion state
const openSections = ref(['background', 'colors']);

function saveSettings() {
    if (!selectedProfile.value) return;

    settingsForm.put(`/admin/profiles/${selectedProfile.value.id}/settings`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => swal.success('¡Diseño guardado!'),
        onError: () => swal.error('Error al guardar'),
    });
}

function resetToDefaults() {
    settingsForm.background_type = 'solid';
    settingsForm.background_color = '#ffffff';
    settingsForm.background_gradient_start = '#3b82f6';
    settingsForm.background_gradient_end = '#8b5cf6';
    settingsForm.background_gradient_direction = 'to-b';
    settingsForm.background_overlay_opacity = 0;
    settingsForm.background_pattern = 'none';
    settingsForm.background_pattern_opacity = 10;
    settingsForm.primary_color = '#3b82f6';
    settingsForm.secondary_color = '#8b5cf6';
    settingsForm.text_color = '#1f2937';
    settingsForm.text_secondary_color = '#6b7280';
    settingsForm.card_style = 'solid';
    settingsForm.card_background_color = '#ffffff';
    settingsForm.card_border_radius = 'lg';
    settingsForm.card_shadow = true;
    settingsForm.card_glow_enabled = false;
    settingsForm.card_glow_variant = 'primary';
    settingsForm.animation_entrance = 'fade';
    settingsForm.animation_hover = 'lift';
    settingsForm.animation_delay = 100;
    settingsForm.show_particles = false;
    settingsForm.particles_style = 'dots';
}

// Create computed settings for preview
const previewSettings = computed(() => ({
    ...settingsForm.data(),
}));

const previewProfile = computed(() => {
    if (!selectedProfile.value) return null;
    return {
        ...selectedProfile.value,
        settings: previewSettings.value,
    };
});

// Background type options
const backgroundTypes = [
    { value: 'solid', label: 'Color sólido', icon: Square },
    { value: 'gradient', label: 'Gradiente', icon: Palette },
    { value: 'image', label: 'Imagen', icon: Image },
];

// Gradient directions
const gradientDirections = [
    { value: 'to-b', label: 'Vertical ↓' },
    { value: 'to-r', label: 'Horizontal →' },
    { value: 'to-br', label: 'Diagonal ↘' },
    { value: 'to-bl', label: 'Diagonal ↙' },
];

// Background patterns
const backgroundPatterns = [
    { value: 'none', label: 'Ninguno' },
    { value: 'dots', label: 'Puntos' },
    { value: 'grid', label: 'Cuadrícula' },
    { value: 'waves', label: 'Ondas' },
    { value: 'noise', label: 'Ruido' },
];

// Card styles
const cardStyles = [
    { value: 'solid', label: 'Sólido' },
    { value: 'transparent', label: 'Transparente' },
    { value: 'glass', label: 'Cristal' },
];

// Border radius options
const borderRadiusOptions = [
    { value: 'none', label: 'Sin bordes' },
    { value: 'sm', label: 'Pequeño' },
    { value: 'md', label: 'Mediano' },
    { value: 'lg', label: 'Grande' },
    { value: 'xl', label: 'Extra grande' },
    { value: '2xl', label: 'Redondeado' },
    { value: 'full', label: 'Completo' },
];

// Glow variants
const glowVariants = [
    { value: 'primary', label: 'Primario' },
    { value: 'cyan', label: 'Cian' },
    { value: 'purple', label: 'Púrpura' },
    { value: 'rainbow', label: 'Arcoíris' },
    { value: 'default', label: 'Personalizado' },
];

// Animation options
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

// Particle styles
const particleStyles = [
    { value: 'dots', label: 'Puntos flotantes' },
    { value: 'lines', label: 'Red de líneas' },
    { value: 'confetti', label: 'Confeti' },
    { value: 'snow', label: 'Nieve' },
];

// Photo styles
const photoStyles = [
    { value: 'circle', label: 'Círculo', icon: Circle },
    { value: 'rounded', label: 'Redondeado', icon: RectangleHorizontal },
    { value: 'square', label: 'Cuadrado', icon: Square },
];

// Font families
const fontFamilies = [
    { value: 'Inter', label: 'Inter' },
    { value: 'Roboto', label: 'Roboto' },
    { value: 'Open Sans', label: 'Open Sans' },
    { value: 'Poppins', label: 'Poppins' },
    { value: 'Montserrat', label: 'Montserrat' },
];
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Design Options -->
        <div class="space-y-6">
            <!-- Profile Selector & Actions -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <Label>Perfil:</Label>
                    <select
                        :value="selectedProfileId"
                        @change="emit('selectProfile', Number(($event.target as HTMLSelectElement).value))"
                        class="rounded-lg border bg-background px-3 py-2 text-sm"
                    >
                        <option v-for="profile in organization.profiles" :key="profile.id" :value="profile.id">
                            {{ profile.name }} {{ profile.is_primary ? '(Principal)' : '' }}
                        </option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" @click="resetToDefaults" class="gap-1">
                        <RotateCcw class="h-4 w-4" />
                        Restablecer
                    </Button>
                    <Button size="sm" @click="saveSettings" :disabled="settingsForm.processing" class="gap-1">
                        <Save class="h-4 w-4" />
                        Guardar
                    </Button>
                </div>
            </div>

            <!-- Settings Accordion -->
            <div class="rounded-xl border bg-card">
                <Accordion type="multiple" v-model="openSections" class="w-full">
                    <!-- Background Section -->
                    <AccordionItem value="background">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Image class="h-4 w-4 text-muted-foreground" />
                                <span>Fondo</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <div class="space-y-4">
                                <!-- Background Type -->
                                <div class="space-y-2">
                                    <Label>Tipo de fondo</Label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button
                                            v-for="type in backgroundTypes"
                                            :key="type.value"
                                            @click="settingsForm.background_type = type.value"
                                            class="flex flex-col items-center gap-1 rounded-lg border p-3 text-xs transition-colors"
                                            :class="settingsForm.background_type === type.value
                                                ? 'border-primary bg-primary/5 text-primary'
                                                : 'hover:bg-muted'"
                                        >
                                            <component :is="type.icon" class="h-4 w-4" />
                                            {{ type.label }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Solid Color -->
                                <div v-if="settingsForm.background_type === 'solid'" class="space-y-2">
                                    <ColorPicker
                                        v-model="settingsForm.background_color"
                                        label="Color de fondo"
                                    />
                                </div>

                                <!-- Gradient Colors -->
                                <div v-if="settingsForm.background_type === 'gradient'" class="space-y-4">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <ColorPicker
                                            v-model="settingsForm.background_gradient_start"
                                            label="Color inicial"
                                        />
                                        <ColorPicker
                                            v-model="settingsForm.background_gradient_end"
                                            label="Color final"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Dirección</Label>
                                        <div class="grid grid-cols-4 gap-2">
                                            <button
                                                v-for="dir in gradientDirections"
                                                :key="dir.value"
                                                @click="settingsForm.background_gradient_direction = dir.value"
                                                class="rounded-lg border px-2 py-1.5 text-xs transition-colors"
                                                :class="settingsForm.background_gradient_direction === dir.value
                                                    ? 'border-primary bg-primary/5 text-primary'
                                                    : 'hover:bg-muted'"
                                            >
                                                {{ dir.label }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Background Image -->
                                <div v-if="settingsForm.background_type === 'image'" class="space-y-4">
                                    <ImageUpload
                                        v-model="settingsForm.background_image"
                                        :current-image="selectedProfile?.settings?.background_image"
                                        label="Imagen de fondo"
                                        aspect-ratio="portrait"
                                        :max-size="4"
                                    />
                                    <div class="space-y-2">
                                        <Label>Opacidad del overlay: {{ settingsForm.background_overlay_opacity }}%</Label>
                                        <Slider
                                            :model-value="[settingsForm.background_overlay_opacity]"
                                            @update:model-value="settingsForm.background_overlay_opacity = $event[0]"
                                            :max="100"
                                            :step="5"
                                        />
                                    </div>
                                </div>

                                <!-- Background Pattern -->
                                <div class="space-y-2">
                                    <Label>Patrón de fondo</Label>
                                    <Select v-model="settingsForm.background_pattern">
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

                                <div v-if="settingsForm.background_pattern !== 'none'" class="space-y-2">
                                    <Label>Opacidad del patrón: {{ settingsForm.background_pattern_opacity }}%</Label>
                                    <Slider
                                        :model-value="[settingsForm.background_pattern_opacity]"
                                        @update:model-value="settingsForm.background_pattern_opacity = $event[0]"
                                        :max="50"
                                        :step="5"
                                    />
                                </div>
                            </div>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Colors Section -->
                    <AccordionItem value="colors">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Palette class="h-4 w-4 text-muted-foreground" />
                                <span>Colores</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <ColorPicker
                                    v-model="settingsForm.primary_color"
                                    label="Color primario"
                                />
                                <ColorPicker
                                    v-model="settingsForm.secondary_color"
                                    label="Color secundario"
                                />
                                <ColorPicker
                                    v-model="settingsForm.text_color"
                                    label="Color de texto"
                                />
                                <ColorPicker
                                    v-model="settingsForm.text_secondary_color"
                                    label="Texto secundario"
                                />
                            </div>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Cards Section -->
                    <AccordionItem value="cards">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Layers class="h-4 w-4 text-muted-foreground" />
                                <span>Tarjetas</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Estilo de tarjeta</Label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button
                                            v-for="style in cardStyles"
                                            :key="style.value"
                                            @click="settingsForm.card_style = style.value"
                                            class="rounded-lg border px-3 py-2 text-xs transition-colors"
                                            :class="settingsForm.card_style === style.value
                                                ? 'border-primary bg-primary/5 text-primary'
                                                : 'hover:bg-muted'"
                                        >
                                            {{ style.label }}
                                        </button>
                                    </div>
                                </div>

                                <ColorPicker
                                    v-model="settingsForm.card_background_color"
                                    label="Color de tarjeta"
                                />

                                <div class="space-y-2">
                                    <Label>Bordes redondeados</Label>
                                    <Select v-model="settingsForm.card_border_radius">
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
                                        :checked="settingsForm.card_shadow"
                                        @update:checked="settingsForm.card_shadow = $event"
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
                                            :checked="settingsForm.card_glow_enabled"
                                            @update:checked="settingsForm.card_glow_enabled = $event"
                                        />
                                    </div>

                                    <div v-if="settingsForm.card_glow_enabled" class="space-y-4">
                                        <div class="space-y-2">
                                            <Label>Variante</Label>
                                            <Select v-model="settingsForm.card_glow_variant">
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

                                        <div v-if="settingsForm.card_glow_variant === 'default'" class="grid gap-4 sm:grid-cols-2">
                                            <ColorPicker
                                                v-model="settingsForm.card_glow_color"
                                                label="Color 1"
                                            />
                                            <ColorPicker
                                                v-model="settingsForm.card_glow_color_secondary"
                                                label="Color 2"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Animations Section -->
                    <AccordionItem value="animations">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Wand2 class="h-4 w-4 text-muted-foreground" />
                                <span>Animaciones</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Animación de entrada</Label>
                                    <Select v-model="settingsForm.animation_entrance">
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
                                    <Select v-model="settingsForm.animation_hover">
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
                                    <Label>Retraso entre elementos: {{ settingsForm.animation_delay }}ms</Label>
                                    <Slider
                                        :model-value="[settingsForm.animation_delay]"
                                        @update:model-value="settingsForm.animation_delay = $event[0]"
                                        :min="0"
                                        :max="500"
                                        :step="50"
                                    />
                                </div>
                            </div>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Visual Effects Section -->
                    <AccordionItem value="effects">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Sparkles class="h-4 w-4 text-muted-foreground" />
                                <span>Efectos visuales</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <div class="space-y-4">
                                <!-- Particles -->
                                <div class="space-y-4 rounded-lg border bg-muted/50 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <Label>Partículas animadas</Label>
                                            <p class="text-xs text-muted-foreground">
                                                Añade movimiento al fondo de tu perfil
                                            </p>
                                        </div>
                                        <Switch
                                            :checked="settingsForm.show_particles"
                                            @update:checked="settingsForm.show_particles = $event"
                                        />
                                    </div>

                                    <div v-if="settingsForm.show_particles" class="space-y-4">
                                        <div class="space-y-2">
                                            <Label>Estilo de partículas</Label>
                                            <Select v-model="settingsForm.particles_style">
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="style in particleStyles" :key="style.value" :value="style.value">
                                                        {{ style.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <ColorPicker
                                            v-model="settingsForm.particles_color"
                                            label="Color de partículas (opcional)"
                                        />
                                        <p class="text-xs text-muted-foreground">
                                            Si no se especifica, usará los colores primario y secundario
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Layout Section -->
                    <AccordionItem value="layout">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Type class="h-4 w-4 text-muted-foreground" />
                                <span>Diseño y tipografía</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Fuente</Label>
                                    <Select v-model="settingsForm.font_family">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="font in fontFamilies" :key="font.value" :value="font.value">
                                                {{ font.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="flex items-center justify-between">
                                    <Label>Mostrar foto de perfil</Label>
                                    <Switch
                                        :checked="settingsForm.show_profile_photo"
                                        @update:checked="settingsForm.show_profile_photo = $event"
                                    />
                                </div>

                                <div v-if="settingsForm.show_profile_photo" class="space-y-2">
                                    <Label>Estilo de foto</Label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button
                                            v-for="style in photoStyles"
                                            :key="style.value"
                                            @click="settingsForm.photo_style = style.value"
                                            class="flex flex-col items-center gap-1 rounded-lg border p-3 text-xs transition-colors"
                                            :class="settingsForm.photo_style === style.value
                                                ? 'border-primary bg-primary/5 text-primary'
                                                : 'hover:bg-muted'"
                                        >
                                            <component :is="style.icon" class="h-4 w-4" />
                                            {{ style.label }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>
        </div>

        <!-- Preview -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Vista previa</h2>
                <span class="rounded-full bg-muted px-2 py-1 text-xs text-muted-foreground">
                    Tiempo real
                </span>
            </div>
            <ProfilePreview
                v-if="previewProfile"
                :profile="previewProfile"
                :organization="organization"
            />
            <p v-else class="text-center text-muted-foreground py-12">
                Selecciona un perfil para ver la vista previa
            </p>
        </div>
    </div>
</template>
