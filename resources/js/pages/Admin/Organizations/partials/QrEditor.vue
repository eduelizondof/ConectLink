<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import {
    QrCode,
    Download,
    Save,
    Palette,
    Square,
    Circle,
    Image,
    RefreshCw,
    CreditCard,
    Eye,
} from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Slider } from '@/components/ui/slider';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ColorPicker } from '@/components/ui/color-picker';
import QrPreview from './QrPreview.vue';

interface QrSettings {
    foreground_color: string;
    background_color: string;
    dot_style: string;
    corner_style: string;
    corner_color: string | null;
    show_logo: boolean;
    logo_source: string;
    custom_logo: string | null;
    logo_size: number;
    logo_shape: string;
    logo_background_color: string;
    logo_background_enabled: boolean;
    logo_margin: number;
    error_correction: string;
    size: number;
    use_gradient: boolean;
    gradient_start_color: string | null;
    gradient_end_color: string | null;
    gradient_type: string;
    gradient_rotation: number;
}

interface Profile {
    id: number;
    name: string;
    photo_url?: string;
    is_primary: boolean;
    qr_settings?: QrSettings;
}

interface Organization {
    id: number;
    name: string;
    slug: string;
    logo_url?: string;
}

const props = defineProps<{
    profile: Profile;
    organization: Organization;
}>();

const swal = useSwal();
const isLoading = ref(false);

// Default QR settings
const defaultSettings: QrSettings = {
    foreground_color: '#000000',
    background_color: '#FFFFFF',
    dot_style: 'square',
    corner_style: 'square',
    corner_color: null,
    show_logo: false,
    logo_source: 'organization',
    custom_logo: null,
    logo_size: 20,
    logo_shape: 'circle',
    logo_background_color: '#FFFFFF',
    logo_background_enabled: true,
    logo_margin: 5,
    error_correction: 'H',
    size: 300,
    use_gradient: false,
    gradient_start_color: null,
    gradient_end_color: null,
    gradient_type: 'linear',
    gradient_rotation: 0,
};

// Form with settings
const form = useForm<QrSettings & { custom_logo_file: File | null }>({
    ...defaultSettings,
    ...props.profile.qr_settings,
    custom_logo_file: null,
});

// Options
const dotStyles = [
    { value: 'square', label: 'Cuadrado' },
    { value: 'rounded', label: 'Redondeado' },
    { value: 'dots', label: 'Puntos' },
    { value: 'classy', label: 'Clásico' },
    { value: 'classy-rounded', label: 'Clásico Redondeado' },
    { value: 'extra-rounded', label: 'Extra Redondeado' },
];

const cornerStyles = [
    { value: 'square', label: 'Cuadrado' },
    { value: 'rounded', label: 'Redondeado' },
    { value: 'extra-rounded', label: 'Extra Redondeado' },
];

const logoShapes = [
    { value: 'square', label: 'Cuadrado' },
    { value: 'rounded', label: 'Redondeado' },
    { value: 'circle', label: 'Circular' },
];

const logoSources = [
    { value: 'organization', label: 'Logo de Organización' },
    { value: 'profile', label: 'Foto de Perfil' },
    { value: 'custom', label: 'Personalizado' },
];

const errorCorrections = [
    { value: 'L', label: 'Bajo (7%)' },
    { value: 'M', label: 'Medio (15%)' },
    { value: 'Q', label: 'Alto (25%)' },
    { value: 'H', label: 'Máximo (30%)' },
];

const gradientTypes = [
    { value: 'linear', label: 'Lineal' },
    { value: 'radial', label: 'Radial' },
];

// Computed profile URL
const profileUrl = computed(() => {
    const baseUrl = window.location.origin;
    if (props.profile.is_primary) {
        return `${baseUrl}/${props.organization.slug}`;
    }
    return `${baseUrl}/${props.organization.slug}/${props.profile.slug}`;
});

// Track blob URLs for cleanup
const blobUrls = ref<string[]>([]);

// Get logo URL based on source
const logoUrl = computed(() => {
    if (!form.show_logo) return null;

    let url: string | null = null;

    switch (form.logo_source) {
        case 'organization':
            url = props.organization.logo_url || null;
            break;
        case 'profile':
            url = props.profile.photo_url || null;
            break;
        case 'custom':
            if (form.custom_logo_file instanceof File) {
                // If it's a File object, create a local URL
                const blobUrl = URL.createObjectURL(form.custom_logo_file);
                blobUrls.value.push(blobUrl);
                url = blobUrl;
            } else if (form.custom_logo) {
                // If it's a path string, convert to full URL
                url = form.custom_logo.startsWith('http') 
                    ? form.custom_logo 
                    : `${window.location.origin}/storage/${form.custom_logo}`;
            }
            break;
        default:
            url = null;
    }

    return url;
});

// Cleanup blob URLs on unmount
onBeforeUnmount(() => {
    blobUrls.value.forEach((url) => {
        URL.revokeObjectURL(url);
    });
    blobUrls.value = [];
});

// Watch for profile changes
watch(() => props.profile.qr_settings, (newSettings) => {
    if (newSettings) {
        // Merge with defaults to ensure all fields are present
        const mergedSettings = { ...defaultSettings, ...newSettings };
        Object.keys(mergedSettings).forEach((key) => {
            if (key in form) {
                form[key] = mergedSettings[key];
            }
        });
    }
}, { deep: true, immediate: true });

// Save settings
function saveSettings() {
    const formData = new FormData();

    // Add all form fields except custom_logo_file (handled separately)
    Object.entries(form.data()).forEach(([key, value]) => {
        if (key === 'custom_logo_file') {
            // Handle file upload separately
            if (value instanceof File) {
                formData.append('custom_logo', value);
            }
        } else if (value !== null && value !== undefined) {
            // Convert boolean to string for FormData
            if (typeof value === 'boolean') {
                formData.append(key, value ? '1' : '0');
            } else {
                formData.append(key, String(value));
            }
        }
    });

    // Use POST with _method spoofing for file upload
    formData.append('_method', 'PUT');

    router.post(`/admin/profiles/${props.profile.id}/qr-settings`, formData, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            swal.success('¡Configuración del QR guardada!');
            // Reload the page data to get updated settings
            router.reload({ only: ['organization'] });
        },
        onError: (errors) => {
            console.error('Error al guardar:', errors);
            swal.error('Error al guardar la configuración. Por favor, verifica los datos.');
        },
    });
}

// Download QR
const qrPreviewRef = ref<InstanceType<typeof QrPreview> | null>(null);

function downloadQr(format: 'png' | 'svg' = 'png') {
    qrPreviewRef.value?.download(format);
}

// Download business card
function downloadBusinessCard() {
    window.open(`/admin/profiles/${props.profile.id}/business-card`, '_blank');
}

// Preview business card
function previewBusinessCard() {
    window.open(`/admin/profiles/${props.profile.id}/business-card/preview`, '_blank');
}

// Handle custom logo file
function handleLogoFile(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        form.custom_logo_file = input.files[0];
    }
}
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Settings Panel -->
            <div class="space-y-6">
                <div class="rounded-xl border bg-card p-6">
                    <div class="mb-6 flex items-center gap-2">
                        <QrCode class="h-5 w-5 text-primary" />
                        <h2 class="text-lg font-semibold">Configuración del QR</h2>
                    </div>

                    <div class="space-y-6">
                        <!-- Colors Section -->
                        <div class="space-y-4">
                            <h3 class="flex items-center gap-2 text-sm font-medium">
                                <Palette class="h-4 w-4" />
                                Colores
                            </h3>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Color Principal</Label>
                                    <ColorPicker v-model="form.foreground_color" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Color de Fondo</Label>
                                    <ColorPicker v-model="form.background_color" />
                                </div>
                            </div>

                            <!-- Gradient -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <Checkbox
                                        id="use_gradient"
                                        :checked="form.use_gradient"
                                        @update:checked="form.use_gradient = $event"
                                    />
                                    <Label for="use_gradient" class="cursor-pointer">
                                        Usar degradado
                                    </Label>
                                </div>

                                <div v-if="form.use_gradient" class="grid gap-4 sm:grid-cols-2 pl-6">
                                    <div class="space-y-2">
                                        <Label>Color Inicial</Label>
                                        <ColorPicker v-model="form.gradient_start_color" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Color Final</Label>
                                        <ColorPicker v-model="form.gradient_end_color" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Tipo</Label>
                                        <Select v-model="form.gradient_type">
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="opt in gradientTypes"
                                                    :key="opt.value"
                                                    :value="opt.value"
                                                >
                                                    {{ opt.label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Rotación: {{ form.gradient_rotation }}°</Label>
                                        <Slider
                                            :model-value="[form.gradient_rotation]"
                                            @update:model-value="form.gradient_rotation = $event[0]"
                                            :min="0"
                                            :max="360"
                                            :step="15"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Style Section -->
                        <div class="space-y-4 border-t pt-4">
                            <h3 class="flex items-center gap-2 text-sm font-medium">
                                <Square class="h-4 w-4" />
                                Estilo
                            </h3>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Estilo de Puntos</Label>
                                    <Select v-model="form.dot_style">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="opt in dotStyles"
                                                :key="opt.value"
                                                :value="opt.value"
                                            >
                                                {{ opt.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label>Estilo de Esquinas</Label>
                                    <Select v-model="form.corner_style">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="opt in cornerStyles"
                                                :key="opt.value"
                                                :value="opt.value"
                                            >
                                                {{ opt.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label>Color de Esquinas (opcional)</Label>
                                <ColorPicker v-model="form.corner_color" />
                            </div>
                        </div>

                        <!-- Logo Section -->
                        <div class="space-y-4 border-t pt-4">
                            <h3 class="flex items-center gap-2 text-sm font-medium">
                                <Image class="h-4 w-4" />
                                Logo Central
                            </h3>

                            <div class="flex items-center gap-3">
                                <Checkbox
                                    id="show_logo"
                                    :checked="form.show_logo"
                                    @update:checked="form.show_logo = $event"
                                />
                                <Label for="show_logo" class="cursor-pointer">
                                    Mostrar logo en el centro del QR
                                </Label>
                            </div>

                            <div v-if="form.show_logo" class="space-y-4 pl-6">
                                <div class="space-y-2">
                                    <Label>Origen del Logo</Label>
                                    <Select v-model="form.logo_source">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="opt in logoSources"
                                                :key="opt.value"
                                                :value="opt.value"
                                            >
                                                {{ opt.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div v-if="form.logo_source === 'custom'" class="space-y-2">
                                    <Label>Logo Personalizado</Label>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="handleLogoFile"
                                        class="w-full rounded-lg border bg-background px-4 py-2 text-sm"
                                    />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label>Forma del Logo</Label>
                                        <Select v-model="form.logo_shape">
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="opt in logoShapes"
                                                    :key="opt.value"
                                                    :value="opt.value"
                                                >
                                                    {{ opt.label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Tamaño: {{ form.logo_size }}%</Label>
                                        <Slider
                                            :model-value="[form.logo_size]"
                                            @update:model-value="form.logo_size = $event[0]"
                                            :min="10"
                                            :max="40"
                                            :step="5"
                                        />
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <Checkbox
                                        id="logo_background_enabled"
                                        :checked="form.logo_background_enabled"
                                        @update:checked="form.logo_background_enabled = $event"
                                    />
                                    <Label for="logo_background_enabled" class="cursor-pointer">
                                        Fondo del logo
                                    </Label>
                                </div>

                                <div v-if="form.logo_background_enabled" class="space-y-2">
                                    <Label>Color de Fondo del Logo</Label>
                                    <ColorPicker v-model="form.logo_background_color" />
                                </div>

                                <div class="space-y-2">
                                    <Label>Margen: {{ form.logo_margin }}px</Label>
                                    <Slider
                                        :model-value="[form.logo_margin]"
                                        @update:model-value="form.logo_margin = $event[0]"
                                        :min="0"
                                        :max="20"
                                        :step="1"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Section -->
                        <div class="space-y-4 border-t pt-4">
                            <h3 class="flex items-center gap-2 text-sm font-medium">
                                <RefreshCw class="h-4 w-4" />
                                Opciones Avanzadas
                            </h3>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Corrección de Errores</Label>
                                    <Select v-model="form.error_correction">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="opt in errorCorrections"
                                                :key="opt.value"
                                                :value="opt.value"
                                            >
                                                {{ opt.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p class="text-xs text-muted-foreground">
                                        Mayor nivel permite más daño/logo pero reduce legibilidad
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label>Tamaño: {{ form.size }}px</Label>
                                    <Slider
                                        :model-value="[form.size]"
                                        @update:model-value="form.size = $event[0]"
                                        :min="100"
                                        :max="500"
                                        :step="50"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="border-t pt-4">
                            <Button @click="saveSettings" :disabled="form.processing" class="w-full gap-2">
                                <Save class="h-4 w-4" />
                                Guardar Configuración
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="space-y-6">
                <div class="rounded-xl border bg-card p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="font-semibold">Vista Previa</h2>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" @click="downloadQr('png')" class="gap-1">
                                <Download class="h-3 w-3" />
                                PNG
                            </Button>
                            <Button variant="outline" size="sm" @click="downloadQr('svg')" class="gap-1">
                                <Download class="h-3 w-3" />
                                SVG
                            </Button>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <QrPreview
                            ref="qrPreviewRef"
                            :url="profileUrl"
                            :settings="form"
                            :logo-url="logoUrl"
                        />
                    </div>

                    <div class="mt-4 text-center">
                        <p class="text-sm text-muted-foreground break-all">{{ profileUrl }}</p>
                    </div>
                </div>

                <!-- Business Card Section -->
                <div class="rounded-xl border bg-card p-6">
                    <div class="mb-4 flex items-center gap-2">
                        <CreditCard class="h-5 w-5 text-primary" />
                        <h2 class="font-semibold">Tarjeta de Presentación</h2>
                    </div>

                    <p class="mb-4 text-sm text-muted-foreground">
                        Genera una tarjeta de presentación profesional con tu QR code y datos de contacto.
                    </p>

                    <div class="flex gap-3">
                        <Button variant="outline" @click="previewBusinessCard" class="flex-1 gap-2">
                            <Eye class="h-4 w-4" />
                            Vista Previa
                        </Button>
                        <Button @click="downloadBusinessCard" class="flex-1 gap-2">
                            <Download class="h-4 w-4" />
                            Descargar PDF
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
