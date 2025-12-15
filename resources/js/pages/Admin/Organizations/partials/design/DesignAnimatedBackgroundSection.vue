<script setup lang="ts">
import { computed, watch, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Image, Save, Loader2, X, Trash2, AlertTriangle } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
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
    background_type: 'animated',
    background_animated_media: null as File | null,
    background_animated_media_type: 'image' as 'image' | 'gif' | 'video',
    background_animated_overlay_opacity: 0,
});

const showFileWarning = ref(false);
const hasExistingAnimatedBackground = computed(() => !!props.profile?.settings?.background_animated_media);
const isDeleted = ref(false); // Track if background was deleted

// Load settings when profile changes
watch(() => props.profile, (profile) => {
    if (profile?.settings) {
        const s = profile.settings;
        form.background_animated_overlay_opacity = s.background_animated_overlay_opacity || 0;
        form.background_animated_media_type = s.background_animated_media_type || 'image';
        form.background_animated_media = null;
        // Reset deleted flag when profile changes
        isDeleted.value = false;
    }
}, { immediate: true });

// Get current media URL for preview
const currentMediaUrl = computed(() => {
    // Don't show preview if background was deleted
    if (isDeleted.value) {
        return null;
    }
    if (form.background_animated_media) {
        return URL.createObjectURL(form.background_animated_media);
    }
    if (props.profile?.settings?.background_animated_media) {
        return `/storage/${props.profile.settings.background_animated_media}`;
    }
    return null;
});

// Get media type from file
function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.background_animated_media = file;
        
        // Reset deleted flag when new file is selected
        isDeleted.value = false;
        
        // Show warning about affecting other backgrounds
        showFileWarning.value = true;
        
        // Determine media type
        const extension = file.name.split('.').pop()?.toLowerCase();
        if (['mp4', 'webm', 'ogg'].includes(extension || '')) {
            form.background_animated_media_type = 'video';
        } else if (extension === 'gif') {
            form.background_animated_media_type = 'gif';
        } else {
            form.background_animated_media_type = 'image';
        }
    }
}

function removeMedia() {
    form.background_animated_media = null;
    showFileWarning.value = false;
    // Don't reset isDeleted here - if there's existing media, it should show
    // Reset file input
    const input = document.getElementById('animated-media-input') as HTMLInputElement;
    if (input) {
        input.value = '';
    }
}

async function deleteAnimatedBackground() {
    if (!props.profile) return;

    const confirmed = await swal.confirm({
        title: '¿Eliminar fondo animado?',
        text: 'Esto eliminará el fondo animado y volverá al fondo configurado (sólido o gradiente).',
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
    });

    if (confirmed) {
        // Change background_type to solid and remove animated media
        // Use a special flag to indicate deletion
        const deleteForm = useForm({
            background_type: 'solid',
            background_color: props.profile?.settings?.background_color || '#ffffff',
            delete_animated_background: true, // Flag to indicate deletion
        });

        deleteForm.put(`/admin/profiles/${props.profile.id}/design/background`, {
            preserveScroll: true,
            onSuccess: () => {
                swal.success('¡Fondo animado eliminado!');
                emit('updated');
                // Reset form and hide preview
                form.background_animated_media = null;
                form.background_animated_overlay_opacity = 0;
                showFileWarning.value = false;
                isDeleted.value = true;
                // Reset file input
                const input = document.getElementById('animated-media-input') as HTMLInputElement;
                if (input) {
                    input.value = '';
                }
            },
            onError: (errors) => {
                console.error('Error deleting animated background:', errors);
                swal.error('Error al eliminar. Intenta de nuevo.');
            },
        });
    }
}

function save() {
    if (!props.profile) return;

    // Ensure background_type is always set
    form.background_type = 'animated';

    // Only use forceFormData if there's a media file
    const hasMediaFile = form.background_animated_media instanceof File;

    // Use transform with _method for PUT when there's a file, similar to ProfileEditor
    if (hasMediaFile) {
        form
            .transform((data) => ({
                ...data,
                _method: 'PUT',
            }))
            .post(`/admin/profiles/${props.profile.id}/design/background`, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    swal.success('¡Fondo animado guardado!');
                    emit('updated');
                    // Reset media after successful save
                    form.background_animated_media = null;
                    showFileWarning.value = false;
                },
                onError: (errors) => {
                    console.error('Error saving animated background:', errors);
                    swal.error('Error al guardar. Verifica los datos e intenta de nuevo.');
                },
            });
    } else {
        // No file, use PUT normally
        // Transform to remove null file and only send necessary fields
        form
            .transform((data) => {
                const transformed: Record<string, any> = {
                    background_type: data.background_type,
                    background_animated_overlay_opacity: data.background_animated_overlay_opacity,
                };
                // Only include media_type if there's existing media
                if (props.profile?.settings?.background_animated_media && data.background_animated_media_type) {
                    transformed.background_animated_media_type = data.background_animated_media_type;
                }
                return transformed;
            })
            .put(`/admin/profiles/${props.profile.id}/design/background`, {
                preserveScroll: true,
                onSuccess: () => {
                    swal.success('¡Fondo animado guardado!');
                    emit('updated');
                    showFileWarning.value = false;
                },
                onError: (errors) => {
                    console.error('Error saving animated background:', errors);
                    swal.error('Error al guardar. Verifica los datos e intenta de nuevo.');
                },
            });
    }
}

// Expose form data for preview
defineExpose({
    formData: computed(() => form.data()),
});
</script>

<template>
    <div class="space-y-4">
        <div class="space-y-2">
            <Label>Imágen de fondo</Label>
            <p class="text-xs text-muted-foreground">
                Sube una imágen para usar como fondo
            </p>
            
            <!-- Warning Alert -->
            <div
                v-if="showFileWarning"
                class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200"
            >
                <AlertTriangle class="h-4 w-4 mt-0.5 flex-shrink-0" />
                <div>
                    <p class="font-medium">Advertencia</p>
                    <p class="text-xs mt-1">
                        Al guardar esta imágen de fondo, se mostrará por encima del fondo configurado (sólido o gradiente) y lo reemplazará visualmente.
                    </p>
                </div>
                <button
                    @click="showFileWarning = false"
                    class="ml-auto p-1 rounded hover:bg-amber-100 dark:hover:bg-amber-900 transition-colors"
                >
                    <X class="h-3 w-3" />
                </button>
            </div>
        </div>

        <!-- Media Upload -->
        <div class="space-y-2">
            <Label>Archivo (Imágen)</Label>
            <div class="space-y-2">
                <!-- File Input -->
                <div class="flex items-center gap-2">
                    <input
                        id="animated-media-input"
                        type="file"
                        accept="image/*,video/*,.gif"
                        @change="handleFileChange"
                        class="hidden"
                    />
                    <label
                        for="animated-media-input"
                        class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer hover:bg-muted transition-colors"
                    >
                        <Image class="h-4 w-4" />
                        <span class="text-sm">Seleccionar archivo</span>
                    </label>
                </div>

                <!-- Preview -->
                <div v-if="currentMediaUrl" class="relative rounded-lg overflow-hidden border bg-muted/50">
                    <!-- Video Preview -->
                    <video
                        v-if="form.background_animated_media_type === 'video'"
                        :src="currentMediaUrl"
                        autoplay
                        loop
                        muted
                        class="w-full h-48 object-cover"
                    />
                    <!-- GIF/Image Preview -->
                    <img
                        v-else
                        :src="currentMediaUrl"
                        alt="Preview"
                        class="w-full h-48 object-cover"
                    />
                    <!-- Remove Button -->
                    <button
                        @click="removeMedia"
                        class="absolute top-2 right-2 p-1.5 rounded-full bg-black/50 hover:bg-black/70 text-white transition-colors"
                    >
                        <X class="h-4 w-4" />
                    </button>
                    <!-- Media Type Badge -->
                    <div class="absolute top-2 left-2 px-2 py-1 rounded bg-black/50 text-white text-xs">
                        {{ form.background_animated_media_type === 'video' ? 'Video' : form.background_animated_media_type === 'gif' ? 'GIF' : 'Imagen' }}
                    </div>
                </div>

                <!-- Current Media Info -->
                <div v-if="!currentMediaUrl && profile?.settings?.background_animated_media" class="text-xs text-muted-foreground">
                    Media actual: {{ profile.settings.background_animated_media_type || 'image' }}
                </div>
            </div>
        </div>

        <!-- Overlay Opacity -->
        <div class="space-y-2">
            <Label>Opacidad del fondo: {{ form.background_animated_overlay_opacity }}%</Label>
            <Slider
                :model-value="[form.background_animated_overlay_opacity]"
                @update:model-value="(val) => form.background_animated_overlay_opacity = val?.[0] ?? 0"
                :max="100"
                :step="5"
            />
            <div class="rounded-lg border bg-muted/30 p-3 space-y-1">
                <p class="text-xs font-medium text-foreground">
                    ¿Cómo funciona la opacidad del fondo?
                </p>
                <p class="text-xs text-muted-foreground leading-relaxed">
                    Esta opción agrega una capa oscura sobre tu fondo animado para mejorar la legibilidad del contenido. 
                    <span class="font-medium">0%</span> = fondo completamente visible, 
                    <span class="font-medium">100%</span> = fondo completamente oscuro. 
                    Recomendamos entre 20-40% para un buen balance entre visibilidad del fondo y legibilidad del texto.
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between pt-2">
            <!-- Delete Button (only show if there's an existing animated background) -->
            <Button
                v-if="hasExistingAnimatedBackground"
                @click="deleteAnimatedBackground"
                :disabled="form.processing"
                variant="destructive"
                size="sm"
                class="gap-1.5"
            >
                <Trash2 class="h-4 w-4" />
                Eliminar Fondo de imágen
            </Button>
            <div v-else></div>

            <!-- Save Button -->
            <Button @click="save" :disabled="form.processing" size="sm" class="gap-1.5">
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <Save v-else class="h-4 w-4" />
                Guardar Fondo de imágen
            </Button>
        </div>
    </div>
</template>

