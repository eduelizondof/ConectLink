<script setup lang="ts">
import { ref, computed, onBeforeUnmount, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import {
    Plus,
    Trash2,
    Link2,
    Pencil,
    ChevronUp,
    ChevronDown,
} from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { ColorPicker } from '@/components/ui/color-picker';
import { ImageUpload } from '@/components/ui/image-upload';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    profile: any;
    maxLinks: number;
}>();

const swal = useSwal();
const showAddModal = ref(false);
const showEditModal = ref(false);
const editingLink = ref<any>(null);
const isUnmounting = ref(false);

const form = useForm({
    title: '',
    url: '',
    description: '',
    image: null as File | null,
    image_position: '' as 'left' | 'right' | 'top' | 'bottom' | '',
    image_shape: '' as 'square' | 'circle' | '',
    button_color: null as string | null,
    text_color: null as string | null,
});

const canAddMore = computed(() => (props.profile.custom_links?.length || 0) < props.maxLinks);

const sortedLinks = computed(() => {
    if (!props.profile.custom_links) return [];
    return [...props.profile.custom_links].sort((a: any, b: any) => {
        return (a.sort_order || 0) - (b.sort_order || 0);
    });
});

// Close modals before unmounting to prevent Vue DOM errors
onBeforeUnmount(async () => {
    isUnmounting.value = true;
    showAddModal.value = false;
    showEditModal.value = false;
    await nextTick();
});

function openAddModal() {
    editingLink.value = null;
    form.reset();
    showAddModal.value = true;
}

function openEditModal(link: any) {
    editingLink.value = link;
    form.reset();
    form.title = link.title || '';
    form.url = link.url || '';
    form.description = link.description || '';
    form.image = null;
    form.image_position = link.image_position || '';
    form.image_shape = link.image_shape || '';
    // Use null for empty colors instead of empty string to avoid ColorPicker errors
    form.button_color = link.button_color || null;
    form.text_color = link.text_color || null;
    showEditModal.value = true;
}

function submitAdd() {
    form.post(`/admin/profiles/${props.profile.id}/custom-links`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showAddModal.value = false;
            swal.success('¡Tarjeta agregada!');
        },
    });
}

function submitEdit() {
    if (!editingLink.value) return;
    
    // If there's a new image file, use POST with _method spoofing
    // Otherwise, use PUT normally
    if (form.image instanceof File) {
        form.transform((data) => ({
            ...data,
            button_color: data.button_color || '',
            text_color: data.text_color || '',
            _method: 'PUT',
        })).post(`/admin/custom-links/${editingLink.value.id}`, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                showEditModal.value = false;
                editingLink.value = null;
                swal.success('¡Tarjeta actualizada!');
            },
            onError: (errors) => {
                console.error('Error updating link:', errors);
                swal.error('Error al actualizar la tarjeta');
            },
        });
    } else {
        // No new image, use PUT and exclude image from data
        form.transform((data) => {
            // eslint-disable-next-line @typescript-eslint/no-unused-vars
            const { image, ...rest } = data;
            return {
                ...rest,
                button_color: rest.button_color || '',
                text_color: rest.text_color || '',
            };
        }).put(`/admin/custom-links/${editingLink.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showEditModal.value = false;
                editingLink.value = null;
                swal.success('¡Tarjeta actualizada!');
            },
            onError: (errors) => {
                console.error('Error updating link:', errors);
                swal.error('Error al actualizar la tarjeta');
            },
        });
    }
}

async function deleteLink(link: any) {
    const confirmed = await swal.confirmDelete(link.title);
    if (confirmed) {
        router.delete(`/admin/custom-links/${link.id}`, {
            preserveScroll: true,
        });
    }
}

function moveLink(link: any, direction: 'up' | 'down') {
    // Sort links by sort_order to ensure correct order
    const links = [...(props.profile.custom_links || [])].sort((a: any, b: any) => {
        return (a.sort_order || 0) - (b.sort_order || 0);
    });
    
    const currentIndex = links.findIndex((l: any) => l.id === link.id);
    
    if (currentIndex === -1) return;
    
    const newIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;
    
    if (newIndex < 0 || newIndex >= links.length) return;
    
    // Swap items
    [links[currentIndex], links[newIndex]] = [links[newIndex], links[currentIndex]];
    
    // Prepare items with new sort_order
    const items = links.map((l: any, index: number) => ({
        id: l.id,
        sort_order: index,
    }));
    
    // Save new order
    router.post(`/admin/profiles/${props.profile.id}/custom-links/reorder`, {
        items,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            swal.success('¡Orden actualizado!');
        },
        onError: () => {
            swal.error('Error al actualizar el orden');
        },
    });
}
</script>

<template>
    <div class="rounded-xl border bg-card p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold">Tarjetas Personalizadas</h3>
                <p class="text-sm text-muted-foreground">
                    {{ profile.custom_links?.length || 0 }}/{{ maxLinks }} tarjetas
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

        <!-- Links List -->
        <div v-if="sortedLinks.length" class="space-y-2">
            <div
                v-for="(link, index) in sortedLinks"
                :key="link.id"
                class="flex items-center gap-3 rounded-lg border p-3 transition-all hover:bg-muted/50"
            >
                <!-- Icon/Image -->
                <div
                    v-if="link.image || link.thumbnail"
                    class="flex-shrink-0 overflow-hidden"
                    :class="{
                        'h-10 w-10': link.image_position === 'left' || link.image_position === 'right' || !link.image_position,
                        'h-16 w-16': link.image_position === 'top' || link.image_position === 'bottom',
                        'rounded-lg': link.image_shape === 'square' || !link.image_shape,
                        'rounded-full': link.image_shape === 'circle',
                    }"
                >
                    <img
                        :src="link.image ? `/storage/${link.image}` : (link.thumbnail ? `/storage/${link.thumbnail}` : '')"
                        :alt="link.title"
                        class="h-full w-full object-cover"
                    />
                </div>
                <div
                    v-else
                    class="flex h-10 w-10 items-center justify-center rounded-lg flex-shrink-0"
                    :style="{ backgroundColor: link.button_color || '#3b82f6' }"
                >
                    <Link2 class="h-5 w-5 text-white" />
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="font-medium truncate">{{ link.title }}</p>
                    <p v-if="link.description" class="text-xs text-muted-foreground truncate">
                        {{ link.description }}
                    </p>
                    <p class="text-xs text-muted-foreground truncate">{{ link.url }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <!-- Reorder buttons -->
                    <div class="flex flex-col gap-0.5">
                        <Button
                            size="icon"
                            variant="ghost"
                            class="h-6 w-6"
                            :disabled="index === 0"
                            @click="moveLink(link, 'up')"
                            title="Mover arriba"
                        >
                            <ChevronUp class="h-3 w-3" />
                        </Button>
                        <Button
                            size="icon"
                            variant="ghost"
                            class="h-6 w-6"
                            :disabled="index === sortedLinks.length - 1"
                            @click="moveLink(link, 'down')"
                            title="Mover abajo"
                        >
                            <ChevronDown class="h-3 w-3" />
                        </Button>
                    </div>
                    <Button size="icon" variant="ghost" @click="openEditModal(link)" title="Editar">
                        <Pencil class="h-4 w-4" />
                    </Button>
                    <Button size="icon" variant="ghost" @click="deleteLink(link)">
                        <Trash2 class="h-4 w-4 text-destructive" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8 text-muted-foreground">
            <Link2 class="h-12 w-12 mx-auto mb-3 opacity-50" />
            <p>No hay tarjetas personalizadas</p>
        </div>

        <!-- Add Modal -->
        <Dialog v-model:open="showAddModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-md max-h-[90vh] overflow-y-auto" aria-describedby="add-link-description">
                <DialogHeader>
                    <DialogTitle>Agregar Tarjeta</DialogTitle>
                </DialogHeader>
                <p id="add-link-description" class="sr-only">Formulario para agregar una nueva tarjeta personalizada</p>

                <form @submit.prevent="submitAdd" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="link-title">Título *</Label>
                        <Input
                            id="link-title"
                            v-model="form.title"
                            placeholder="📦 Ver mi catálogo"
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="link-url">URL</Label>
                        <Input
                            id="link-url"
                            v-model="form.url"
                            placeholder="https://... (opcional)"
                        />
                        <p class="text-xs text-muted-foreground">Deja vacío para crear una tarjeta informativa sin enlace</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="link-description">Descripción</Label>
                        <Input
                            id="link-description"
                            v-model="form.description"
                            placeholder="Breve descripción..."
                        />
                    </div>

                    <div class="space-y-2">
                        <Label>Imagen</Label>
                        <ImageUpload
                            v-model="form.image"
                            aspect-ratio="auto"
                            :max-size="2"
                            placeholder="Arrastra una imagen o haz clic para seleccionar"
                        />
                    </div>

                    <div v-if="form.image" class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label>Posición de la imagen</Label>
                            <Select v-model="form.image_position">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar posición" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="left">Izquierda (como icono)</SelectItem>
                                    <SelectItem value="right">Derecha (como icono)</SelectItem>
                                    <SelectItem value="top">Arriba (prominente)</SelectItem>
                                    <SelectItem value="bottom">Abajo (prominente)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label>Forma de la imagen</Label>
                            <Select v-model="form.image_shape">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar forma" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="square">Cuadrada</SelectItem>
                                    <SelectItem value="circle">Redonda</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <ColorPicker
                            v-model="form.button_color"
                            label="Color del botón"
                        />
                        <ColorPicker
                            v-model="form.text_color"
                            label="Color del texto"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showAddModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            Agregar
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Modal -->
        <Dialog v-model:open="showEditModal" :modal="true">
            <DialogContent v-if="!isUnmounting && editingLink" class="max-w-md max-h-[90vh] overflow-y-auto" aria-describedby="edit-link-description">
                <DialogHeader>
                    <DialogTitle>Editar Tarjeta</DialogTitle>
                </DialogHeader>
                <p id="edit-link-description" class="sr-only">Formulario para editar una tarjeta personalizada existente</p>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="edit-link-title">Título *</Label>
                        <Input
                            id="edit-link-title"
                            v-model="form.title"
                            placeholder="📦 Ver mi catálogo"
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-link-url">URL</Label>
                        <Input
                            id="edit-link-url"
                            v-model="form.url"
                            placeholder="https://... (opcional)"
                        />
                        <p class="text-xs text-muted-foreground">Deja vacío para crear una tarjeta informativa sin enlace</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-link-description">Descripción</Label>
                        <Input
                            id="edit-link-description"
                            v-model="form.description"
                            placeholder="Breve descripción..."
                        />
                    </div>

                    <div class="space-y-2">
                        <Label>Imagen</Label>
                        <ImageUpload
                            v-model="form.image"
                            aspect-ratio="auto"
                            :max-size="2"
                            placeholder="Arrastra una imagen o haz clic para seleccionar"
                        />
                        <p v-if="editingLink && (editingLink.image || editingLink.thumbnail) && !form.image" class="text-xs text-muted-foreground">
                            Imagen actual: {{ editingLink.image || editingLink.thumbnail }}
                        </p>
                    </div>

                    <div v-if="form.image || (editingLink && (editingLink.image || editingLink.thumbnail))" class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label>Posición de la imagen</Label>
                            <Select v-model="form.image_position">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar posición" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="left">Izquierda (como icono)</SelectItem>
                                    <SelectItem value="right">Derecha (como icono)</SelectItem>
                                    <SelectItem value="top">Arriba (prominente)</SelectItem>
                                    <SelectItem value="bottom">Abajo (prominente)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label>Forma de la imagen</Label>
                            <Select v-model="form.image_shape">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar forma" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="square">Cuadrada</SelectItem>
                                    <SelectItem value="circle">Redonda</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <ColorPicker
                            v-model="form.button_color"
                            label="Color del botón"
                        />
                        <ColorPicker
                            v-model="form.text_color"
                            label="Color del texto"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showEditModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            Actualizar
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>





