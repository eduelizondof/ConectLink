<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Save, Loader2 } from 'lucide-vue-next';
import { ColorPicker } from '@/components/ui/color-picker';
import { Button } from '@/components/ui/button';

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
    primary_color: '#3b82f6',
    secondary_color: '#8b5cf6',
    text_color: '#1f2937',
    text_secondary_color: '#6b7280',
});

// Load settings when profile changes
watch(() => props.profile, (profile) => {
    if (profile?.settings) {
        const s = profile.settings;
        form.primary_color = s.primary_color || '#3b82f6';
        form.secondary_color = s.secondary_color || '#8b5cf6';
        form.text_color = s.text_color || '#1f2937';
        form.text_secondary_color = s.text_secondary_color || '#6b7280';
    }
}, { immediate: true });

function save() {
    if (!props.profile) return;

    form.put(`/admin/profiles/${props.profile.id}/design/colors`, {
        preserveScroll: true,
        onSuccess: () => {
            swal.success('¡Colores guardados!');
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
        <div class="grid gap-4 sm:grid-cols-2">
            <ColorPicker
                v-model="form.primary_color"
                label="Color primario"
            />
            <ColorPicker
                v-model="form.secondary_color"
                label="Color secundario"
            />
            <ColorPicker
                v-model="form.text_color"
                label="Color de texto"
            />
            <ColorPicker
                v-model="form.text_secondary_color"
                label="Texto secundario"
            />
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-2">
            <Button @click="save" :disabled="form.processing" size="sm" class="gap-1.5">
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <Save v-else class="h-4 w-4" />
                Guardar Colores
            </Button>
        </div>
    </div>
</template>

