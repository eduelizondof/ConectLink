<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { Save, Loader2, Circle, Square, RectangleHorizontal } from 'lucide-vue-next';
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
    font_family: 'Inter',
    font_size: 'base',
    layout_style: 'centered',
    show_profile_photo: true,
    photo_style: 'circle',
    photo_size: 'lg',
    social_style: 'icons',
    social_size: 'md',
    social_colored: true,
});

// Load settings when profile changes
watch(() => props.profile, (profile) => {
    if (profile?.settings) {
        const s = profile.settings;
        form.font_family = s.font_family || 'Inter';
        form.font_size = s.font_size || 'base';
        form.layout_style = s.layout_style || 'centered';
        form.show_profile_photo = s.show_profile_photo ?? true;
        form.photo_style = s.photo_style || 'circle';
        form.photo_size = s.photo_size || 'lg';
        form.social_style = s.social_style || 'icons';
        form.social_size = s.social_size || 'md';
        form.social_colored = s.social_colored ?? true;
    }
}, { immediate: true });

// Options
const fontFamilies = [
    { value: 'Inter', label: 'Inter' },
    { value: 'Roboto', label: 'Roboto' },
    { value: 'Open Sans', label: 'Open Sans' },
    { value: 'Poppins', label: 'Poppins' },
    { value: 'Montserrat', label: 'Montserrat' },
];

const photoStyles = [
    { value: 'circle', label: 'Círculo', icon: Circle },
    { value: 'rounded', label: 'Redondeado', icon: RectangleHorizontal },
    { value: 'square', label: 'Cuadrado', icon: Square },
];

const socialStyles = [
    { value: 'icons', label: 'Iconos' },
    { value: 'buttons', label: 'Botones' },
    { value: 'pills', label: 'Píldoras' },
];

const socialSizes = [
    { value: 'sm', label: 'Pequeño' },
    { value: 'md', label: 'Mediano' },
    { value: 'lg', label: 'Grande' },
];

function save() {
    if (!props.profile) return;

    form.put(`/admin/profiles/${props.profile.id}/design/layout`, {
        preserveScroll: true,
        onSuccess: () => {
            swal.success('¡Diseño guardado!');
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
            <Label>Fuente</Label>
            <Select v-model="form.font_family">
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
                :checked="form.show_profile_photo"
                @update:checked="form.show_profile_photo = $event"
            />
        </div>

        <div v-if="form.show_profile_photo" class="space-y-2">
            <Label>Estilo de foto</Label>
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="style in photoStyles"
                    :key="style.value"
                    @click="form.photo_style = style.value"
                    class="flex flex-col items-center gap-1 rounded-lg border p-3 text-xs transition-colors"
                    :class="form.photo_style === style.value
                        ? 'border-primary bg-primary/5 text-primary'
                        : 'hover:bg-muted'"
                >
                    <component :is="style.icon" class="h-4 w-4" />
                    {{ style.label }}
                </button>
            </div>
        </div>

        <!-- Social Links Style -->
        <div class="space-y-4 rounded-lg border bg-muted/50 p-4">
            <Label class="text-sm font-medium">Redes Sociales</Label>
            
            <div class="space-y-2">
                <Label class="text-xs">Estilo</Label>
                <div class="grid grid-cols-3 gap-2">
                    <button
                        v-for="style in socialStyles"
                        :key="style.value"
                        @click="form.social_style = style.value"
                        class="rounded-lg border px-3 py-2 text-xs transition-colors"
                        :class="form.social_style === style.value
                            ? 'border-primary bg-primary/5 text-primary'
                            : 'hover:bg-muted'"
                    >
                        {{ style.label }}
                    </button>
                </div>
            </div>

            <div class="space-y-2">
                <Label class="text-xs">Tamaño</Label>
                <Select v-model="form.social_size">
                    <SelectTrigger>
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="size in socialSizes" :key="size.value" :value="size.value">
                            {{ size.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="flex items-center justify-between">
                <Label class="text-xs">Usar colores de marca</Label>
                <Switch
                    :checked="form.social_colored"
                    @update:checked="form.social_colored = $event"
                />
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-2">
            <Button @click="save" :disabled="form.processing" size="sm" class="gap-1.5">
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <Save v-else class="h-4 w-4" />
                Guardar Diseño
            </Button>
        </div>
    </div>
</template>

