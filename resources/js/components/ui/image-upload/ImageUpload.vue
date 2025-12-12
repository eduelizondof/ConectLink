<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Upload, X, Image as ImageIcon } from 'lucide-vue-next';

const props = defineProps<{
    modelValue?: File | null;
    currentImage?: string | null;
    label?: string;
    accept?: string;
    maxSize?: number; // in MB
    aspectRatio?: 'square' | 'video' | 'auto';
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: File | null];
    'remove': [];
}>();

const isDragging = ref(false);
const previewUrl = ref<string | null>(null);
const error = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const aspectClass = computed(() => {
    switch (props.aspectRatio) {
        case 'square': return 'aspect-square';
        case 'video': return 'aspect-video';
        default: return 'min-h-[200px]';
    }
});

const displayImage = computed(() => {
    if (previewUrl.value) return previewUrl.value;
    if (props.currentImage) {
        // Handle different image path formats
        if (props.currentImage.startsWith('http') || props.currentImage.startsWith('/')) {
            return props.currentImage;
        }
        return `/storage/${props.currentImage}`;
    }
    return null;
});

// Clear preview when modelValue is cleared or currentImage changes
watch(() => props.modelValue, (newValue) => {
    if (!newValue && !props.currentImage) {
        previewUrl.value = null;
    }
});

watch(() => props.currentImage, () => {
    // Only clear preview if modelValue is also null/cleared
    if (!props.modelValue) {
        previewUrl.value = null;
    }
});

function handleFile(file: File) {
    error.value = null;

    // Validate file type
    const accept = props.accept || 'image/*';
    if (!file.type.match(accept.replace('*', '.*'))) {
        error.value = 'Tipo de archivo no válido';
        return;
    }

    // Validate file size
    const maxSize = props.maxSize || 2; // 2MB default
    if (file.size > maxSize * 1024 * 1024) {
        error.value = `El archivo es muy grande (máx ${maxSize}MB)`;
        return;
    }

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);

    emit('update:modelValue', file);
}

function onDrop(e: DragEvent) {
    isDragging.value = false;
    const file = e.dataTransfer?.files[0];
    if (file) handleFile(file);
}

function onFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        handleFile(file);
    }
    // Reset input to allow selecting the same file again
    if (target) {
        target.value = '';
    }
}

function triggerFileInput() {
    fileInputRef.value?.click();
}

function removeImage() {
    previewUrl.value = null;
    emit('update:modelValue', null);
    emit('remove');
}
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" class="text-sm font-medium">{{ label }}</label>

        <!-- Hidden file input -->
        <input
            ref="fileInputRef"
            type="file"
            class="sr-only"
            :accept="accept || 'image/*'"
            @change="onFileChange"
        />

        <div
            class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed transition-all"
            :class="[
                aspectClass,
                isDragging ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/50',
                displayImage ? 'p-0' : 'p-6 cursor-pointer',
            ]"
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="onDrop"
            @click="!displayImage && triggerFileInput()"
        >
            <!-- Preview -->
            <template v-if="displayImage">
                <img
                    :src="displayImage"
                    :alt="label || 'Preview'"
                    class="h-full w-full rounded-xl object-cover cursor-pointer"
                    @click="triggerFileInput"
                />
                <button
                    type="button"
                    class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-destructive text-destructive-foreground shadow-lg hover:bg-destructive/90"
                    @click.stop="removeImage"
                >
                    <X class="h-4 w-4" />
                </button>
            </template>

            <!-- Upload Prompt -->
            <template v-else>
                <div class="flex flex-col items-center text-center pointer-events-none">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-muted">
                        <ImageIcon class="h-7 w-7 text-muted-foreground" />
                    </div>
                    <p class="mt-4 text-sm font-medium">
                        {{ placeholder || 'Arrastra una imagen aquí' }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        o haz clic para seleccionar
                    </p>
                    <button
                        type="button"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground pointer-events-auto hover:bg-primary/90"
                        @click.stop="triggerFileInput"
                    >
                        <Upload class="h-4 w-4" />
                        Subir Imagen
                    </button>
                </div>
            </template>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="text-sm text-destructive">{{ error }}</p>
    </div>
</template>





