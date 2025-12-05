<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import {
    Plus,
    Trash2,
    Bell,
    Info,
    Tag,
    AlertTriangle,
    CheckCircle,
    Megaphone,
} from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { ColorPicker } from '@/components/ui/color-picker';
import { RadioCard } from '@/components/ui/radio-card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    profile: any;
    maxAlerts: number;
}>();

const swal = useSwal();
const showAddModal = ref(false);

const form = useForm({
    title: '',
    message: '',
    type: 'info',
    link_url: '',
    link_text: '',
    position: 'bottom-right',
    animation: 'bounce',
    background_color: '',
    is_dismissible: true,
    show_on_load: true,
    delay_seconds: 3,
});

const canAddMore = (props.profile.floating_alerts?.length || 0) < props.maxAlerts;

const alertTypes = [
    { value: 'info', label: 'Info', icon: Info, color: '#3B82F6' },
    { value: 'promo', label: 'Promo', icon: Tag, color: '#10B981' },
    { value: 'warning', label: 'Aviso', icon: AlertTriangle, color: '#F59E0B' },
    { value: 'success', label: 'Éxito', icon: CheckCircle, color: '#10B981' },
    { value: 'announcement', label: 'Anuncio', icon: Megaphone, color: '#8B5CF6' },
];

const positions = [
    { value: 'top', label: 'Arriba' },
    { value: 'bottom', label: 'Abajo' },
    { value: 'top-right', label: 'Arriba Der.' },
    { value: 'top-left', label: 'Arriba Izq.' },
    { value: 'bottom-right', label: 'Abajo Der.' },
    { value: 'bottom-left', label: 'Abajo Izq.' },
];

const animations = [
    { value: 'none', label: 'Ninguna' },
    { value: 'bounce', label: 'Rebote' },
    { value: 'pulse', label: 'Pulso' },
    { value: 'shake', label: 'Vibrar' },
    { value: 'slide', label: 'Deslizar' },
];

function getTypeIcon(type: string) {
    return alertTypes.find(t => t.value === type)?.icon || Info;
}

function getTypeColor(type: string) {
    return alertTypes.find(t => t.value === type)?.color || '#3B82F6';
}

function openAddModal() {
    form.reset();
    form.type = 'info';
    form.position = 'bottom-right';
    form.animation = 'bounce';
    form.is_dismissible = true;
    form.show_on_load = true;
    form.delay_seconds = 3;
    showAddModal.value = true;
}

function submitAdd() {
    form.post(`/admin/profiles/${props.profile.id}/floating-alerts`, {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            swal.success('¡Alerta creada!');
        },
    });
}

async function deleteAlert(alert: any) {
    const confirmed = await swal.confirmDelete(alert.title || 'esta alerta');
    if (confirmed) {
        router.delete(`/admin/floating-alerts/${alert.id}`, {
            preserveScroll: true,
        });
    }
}

function toggleActive(alert: any) {
    router.put(`/admin/floating-alerts/${alert.id}`, {
        ...alert,
        is_active: !alert.is_active,
    }, { preserveScroll: true });
}
</script>

<template>
    <div class="rounded-xl border bg-card p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold">Alertas Flotantes</h3>
                <p class="text-sm text-muted-foreground">
                    {{ profile.floating_alerts?.length || 0 }}/{{ maxAlerts }} alertas
                </p>
            </div>
            <Button
                v-if="canAddMore"
                size="sm"
                @click="openAddModal"
                class="gap-1"
            >
                <Plus class="h-4 w-4" />
                Agregar
            </Button>
        </div>

        <!-- Alerts List -->
        <div v-if="profile.floating_alerts?.length" class="space-y-2">
            <div
                v-for="alert in profile.floating_alerts"
                :key="alert.id"
                class="flex items-center gap-3 rounded-lg border p-3 transition-all hover:bg-muted/50"
            >
                <!-- Icon -->
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg text-white"
                    :style="{ backgroundColor: alert.background_color || getTypeColor(alert.type) }"
                >
                    <component :is="getTypeIcon(alert.type)" class="h-5 w-5" />
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p v-if="alert.title" class="font-medium truncate">{{ alert.title }}</p>
                    <p class="text-sm text-muted-foreground truncate">{{ alert.message }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ positions.find(p => p.value === alert.position)?.label }} • {{ animations.find(a => a.value === alert.animation)?.label }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <Checkbox
                        :checked="alert.is_active"
                        @update:checked="toggleActive(alert)"
                    />
                    <Button size="icon" variant="ghost" @click="deleteAlert(alert)">
                        <Trash2 class="h-4 w-4 text-destructive" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8 text-muted-foreground">
            <Bell class="h-12 w-12 mx-auto mb-3 opacity-50" />
            <p>No hay alertas configuradas</p>
        </div>

        <!-- Add Modal -->
        <Dialog v-model:open="showAddModal">
            <DialogContent class="max-w-lg max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Nueva Alerta Flotante</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitAdd" class="space-y-4">
                    <!-- Type -->
                    <div class="space-y-2">
                        <Label>Tipo de alerta</Label>
                        <div class="grid grid-cols-5 gap-2">
                            <button
                                v-for="type in alertTypes"
                                :key="type.value"
                                type="button"
                                class="flex flex-col items-center gap-1 rounded-lg border p-2 transition-all"
                                :class="form.type === type.value ? 'border-primary bg-primary/5' : 'hover:bg-muted'"
                                @click="form.type = type.value"
                            >
                                <component :is="type.icon" class="h-5 w-5" :style="{ color: type.color }" />
                                <span class="text-[10px]">{{ type.label }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="alert-title">Título</Label>
                        <Input
                            id="alert-title"
                            v-model="form.title"
                            placeholder="🎉 ¡Oferta especial!"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="alert-message">Mensaje *</Label>
                        <textarea
                            id="alert-message"
                            v-model="form.message"
                            rows="2"
                            class="w-full rounded-lg border bg-background px-4 py-3 text-sm"
                            placeholder="Descuento del 20% en todos los productos..."
                            required
                        />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="alert-link_url">URL del botón</Label>
                            <Input
                                id="alert-link_url"
                                v-model="form.link_url"
                                placeholder="https://..."
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="alert-link_text">Texto del botón</Label>
                            <Input
                                id="alert-link_text"
                                v-model="form.link_text"
                                placeholder="Ver más"
                            />
                        </div>
                    </div>

                    <!-- Position -->
                    <div class="space-y-2">
                        <Label>Posición</Label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                v-for="pos in positions"
                                :key="pos.value"
                                type="button"
                                class="rounded-lg border px-3 py-2 text-sm transition-all"
                                :class="form.position === pos.value ? 'border-primary bg-primary/5 font-medium' : 'hover:bg-muted'"
                                @click="form.position = pos.value"
                            >
                                {{ pos.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Animation -->
                    <div class="space-y-2">
                        <Label>Animación</Label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="anim in animations"
                                :key="anim.value"
                                type="button"
                                class="rounded-lg border px-3 py-2 text-sm transition-all"
                                :class="form.animation === anim.value ? 'border-primary bg-primary/5 font-medium' : 'hover:bg-muted'"
                                @click="form.animation = anim.value"
                            >
                                {{ anim.label }}
                            </button>
                        </div>
                    </div>

                    <ColorPicker v-model="form.background_color" label="Color de fondo (opcional)" />

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <Checkbox
                                id="alert-dismissible"
                                :checked="form.is_dismissible"
                                @update:checked="form.is_dismissible = $event"
                            />
                            <Label for="alert-dismissible" class="cursor-pointer">
                                Se puede cerrar
                            </Label>
                        </div>
                        <div class="flex items-center gap-3">
                            <Checkbox
                                id="alert-show_on_load"
                                :checked="form.show_on_load"
                                @update:checked="form.show_on_load = $event"
                            />
                            <Label for="alert-show_on_load" class="cursor-pointer">
                                Mostrar al cargar la página
                            </Label>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="alert-delay">Retraso (segundos)</Label>
                        <Input
                            id="alert-delay"
                            v-model.number="form.delay_seconds"
                            type="number"
                            min="0"
                            max="30"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showAddModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            Crear Alerta
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>


