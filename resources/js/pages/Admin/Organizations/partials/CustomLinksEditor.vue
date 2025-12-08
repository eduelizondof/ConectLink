<script setup lang="ts">
import { ref, computed, onBeforeUnmount, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import {
    Plus,
    Trash2,
    Link2,
    Star,
    ExternalLink,
} from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { ColorPicker } from '@/components/ui/color-picker';
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
const isUnmounting = ref(false);

const form = useForm({
    title: '',
    url: '',
    description: '',
    button_color: '',
    text_color: '',
    is_highlighted: false,
});

const canAddMore = computed(() => (props.profile.custom_links?.length || 0) < props.maxLinks);

// Close modals before unmounting to prevent Vue DOM errors
onBeforeUnmount(async () => {
    isUnmounting.value = true;
    showAddModal.value = false;
    await nextTick();
});

function openAddModal() {
    form.reset();
    showAddModal.value = true;
}

function submitAdd() {
    form.post(`/admin/profiles/${props.profile.id}/custom-links`, {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            swal.success('¡Link agregado!');
        },
    });
}

async function deleteLink(link: any) {
    const confirmed = await swal.confirmDelete(link.title);
    if (confirmed) {
        router.delete(`/admin/custom-links/${link.id}`, {
            preserveScroll: true,
        });
    }
}

function toggleActive(link: any) {
    router.put(`/admin/custom-links/${link.id}`, {
        ...link,
        is_active: !link.is_active,
    }, { preserveScroll: true });
}

function toggleHighlight(link: any) {
    router.put(`/admin/custom-links/${link.id}`, {
        ...link,
        is_highlighted: !link.is_highlighted,
    }, { preserveScroll: true });
}
</script>

<template>
    <div class="rounded-xl border bg-card p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold">Links Personalizados</h3>
                <p class="text-sm text-muted-foreground">
                    {{ profile.custom_links?.length || 0 }}/{{ maxLinks }} links
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
        <div v-if="profile.custom_links?.length" class="space-y-2">
            <div
                v-for="link in profile.custom_links"
                :key="link.id"
                class="flex items-center gap-3 rounded-lg border p-3 transition-all hover:bg-muted/50"
                :class="{ 'ring-2 ring-amber-400': link.is_highlighted }"
            >
                <!-- Icon/Indicator -->
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg"
                    :style="{ backgroundColor: link.button_color || '#3b82f6' }"
                >
                    <Link2 class="h-5 w-5 text-white" />
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-medium truncate">{{ link.title }}</p>
                        <Star v-if="link.is_highlighted" class="h-4 w-4 text-amber-500 fill-amber-500" />
                    </div>
                    <p v-if="link.description" class="text-xs text-muted-foreground truncate">
                        {{ link.description }}
                    </p>
                    <p class="text-xs text-muted-foreground truncate">{{ link.url }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <button
                        @click="toggleHighlight(link)"
                        class="p-2 rounded-lg hover:bg-muted"
                        :title="link.is_highlighted ? 'Quitar destacado' : 'Destacar'"
                    >
                        <Star
                            class="h-4 w-4"
                            :class="link.is_highlighted ? 'text-amber-500 fill-amber-500' : 'text-muted-foreground'"
                        />
                    </button>
                    <Checkbox
                        :checked="link.is_active"
                        @update:checked="toggleActive(link)"
                    />
                    <Button size="icon" variant="ghost" @click="deleteLink(link)">
                        <Trash2 class="h-4 w-4 text-destructive" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8 text-muted-foreground">
            <Link2 class="h-12 w-12 mx-auto mb-3 opacity-50" />
            <p>No hay links personalizados</p>
        </div>

        <!-- Add Modal -->
        <Dialog v-model:open="showAddModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Agregar Link</DialogTitle>
                </DialogHeader>

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
                        <Label for="link-url">URL *</Label>
                        <Input
                            id="link-url"
                            v-model="form.url"
                            placeholder="https://..."
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="link-description">Descripción</Label>
                        <Input
                            id="link-description"
                            v-model="form.description"
                            placeholder="Breve descripción..."
                        />
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

                    <div class="flex items-center gap-3">
                        <Checkbox
                            id="link-highlighted"
                            :checked="form.is_highlighted"
                            @update:checked="form.is_highlighted = $event"
                        />
                        <Label for="link-highlighted" class="cursor-pointer flex items-center gap-2">
                            <Star class="h-4 w-4 text-amber-500" />
                            Destacar este link
                        </Label>
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
    </div>
</template>





