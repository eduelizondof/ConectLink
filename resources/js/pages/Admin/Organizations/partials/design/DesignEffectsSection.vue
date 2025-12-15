<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Save, Loader2 } from 'lucide-vue-next';
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
    show_particles: false,
    particles_style: 'dots',
    particles_color: '',
});

// Load settings when profile changes
watch(() => props.profile, (profile) => {
    if (profile?.settings) {
        const s = profile.settings;
        form.show_particles = s.show_particles ?? false;
        form.particles_style = s.particles_style || 'dots';
        form.particles_color = s.particles_color || '';
    }
}, { immediate: true });

// Options
const particleStyles = [
    { value: 'dots', label: 'Puntos flotantes' },
    { value: 'lines', label: 'Red de líneas' },
    { value: 'confetti', label: 'Confeti' },
    { value: 'snow', label: 'Nieve' },
];

function save() {
    if (!props.profile) return;

    form.put(`/admin/profiles/${props.profile.id}/design/effects`, {
        preserveScroll: true,
        onSuccess: () => {
            swal.success('¡Efectos guardados!');
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
                    :checked="form.show_particles"
                    @update:checked="form.show_particles = $event"
                />
            </div>

            <div v-if="form.show_particles" class="space-y-4">
                <div class="space-y-2">
                    <Label>Estilo de partículas</Label>
                    <Select v-model="form.particles_style">
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
                    v-model="form.particles_color"
                    label="Color de partículas (opcional)"
                />
                <p class="text-xs text-muted-foreground">
                    Si no se especifica, usará los colores primario y secundario
                </p>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-2">
            <Button @click="save" :disabled="form.processing" size="sm" class="gap-1.5">
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <Save v-else class="h-4 w-4" />
                Guardar Efectos
            </Button>
        </div>
    </div>
</template>

