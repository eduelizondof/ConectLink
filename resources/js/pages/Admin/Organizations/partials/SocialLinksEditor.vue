<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import {
    Plus,
    Trash2,
    GripVertical,
    Facebook,
    Instagram,
    Twitter,
    Linkedin,
    Youtube,
    Github,
    Globe,
    Mail,
    Phone,
    MessageCircle,
    Send,
    Link2,
} from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    profile: any;
    platforms: Record<string, { label: string; icon: string; color: string }>;
    maxLinks: number;
}>();

const swal = useSwal();
const showAddModal = ref(false);

const form = useForm({
    platform: '',
    url: '',
    label: '',
});

const canAddMore = (props.profile.social_links?.length || 0) < props.maxLinks;

const platformIcons: Record<string, any> = {
    facebook: Facebook,
    instagram: Instagram,
    twitter: Twitter,
    linkedin: Linkedin,
    youtube: Youtube,
    github: Github,
    whatsapp: MessageCircle,
    telegram: Send,
    website: Globe,
    email: Mail,
    phone: Phone,
    other: Link2,
};

function getPlatformIcon(platform: string) {
    return platformIcons[platform] || Link2;
}

function openAddModal() {
    form.reset();
    showAddModal.value = true;
}

function submitAdd() {
    form.post(`/admin/profiles/${props.profile.id}/social-links`, {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            swal.success('¡Red social agregada!');
        },
    });
}

async function deleteLink(link: any) {
    const confirmed = await swal.confirmDelete(link.label || props.platforms[link.platform]?.label);
    if (confirmed) {
        router.delete(`/admin/social-links/${link.id}`, {
            preserveScroll: true,
        });
    }
}

function toggleActive(link: any) {
    router.put(`/admin/social-links/${link.id}`, {
        ...link,
        is_active: !link.is_active,
    }, { preserveScroll: true });
}
</script>

<template>
    <div class="rounded-xl border bg-card p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold">Redes Sociales</h3>
                <p class="text-sm text-muted-foreground">
                    {{ profile.social_links?.length || 0 }}/{{ maxLinks }} redes
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
        <div v-if="profile.social_links?.length" class="space-y-2">
            <div
                v-for="link in profile.social_links"
                :key="link.id"
                class="flex items-center gap-3 rounded-lg border p-3 transition-all hover:bg-muted/50"
            >
                <!-- Icon -->
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg"
                    :style="{ backgroundColor: platforms[link.platform]?.color || '#6b7280' }"
                >
                    <component :is="getPlatformIcon(link.platform)" class="h-5 w-5 text-white" />
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="font-medium truncate">
                        {{ link.label || platforms[link.platform]?.label || link.platform }}
                    </p>
                    <p class="text-xs text-muted-foreground truncate">{{ link.url }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
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
            <Globe class="h-12 w-12 mx-auto mb-3 opacity-50" />
            <p>No hay redes sociales configuradas</p>
        </div>

        <!-- Add Modal -->
        <Dialog v-model:open="showAddModal">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Agregar Red Social</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitAdd" class="space-y-4">
                    <!-- Platform Selection -->
                    <div class="space-y-2">
                        <Label>Plataforma *</Label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="(config, key) in platforms"
                                :key="key"
                                type="button"
                                class="flex flex-col items-center gap-1 rounded-lg border p-3 transition-all"
                                :class="form.platform === key ? 'border-primary bg-primary/5' : 'hover:bg-muted'"
                                @click="form.platform = key"
                            >
                                <component :is="getPlatformIcon(key)" class="h-5 w-5" />
                                <span class="text-[10px] truncate w-full text-center">{{ config.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- URL -->
                    <div class="space-y-2">
                        <Label for="social-url">URL / Enlace *</Label>
                        <Input
                            id="social-url"
                            v-model="form.url"
                            :placeholder="form.platform === 'whatsapp' ? 'https://wa.me/521...' : 'https://...'"
                            required
                        />
                    </div>

                    <!-- Custom Label -->
                    <div class="space-y-2">
                        <Label for="social-label">Etiqueta personalizada</Label>
                        <Input
                            id="social-label"
                            v-model="form.label"
                            placeholder="Opcional"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showAddModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="form.processing || !form.platform">
                            Agregar
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>





