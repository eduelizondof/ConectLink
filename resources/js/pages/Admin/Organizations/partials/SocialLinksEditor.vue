<script setup lang="ts">
import { ref, computed, onBeforeUnmount, nextTick, h } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import {
    Plus,
    Trash2,
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
    Music,
    Twitch,
    Dribbble,
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

// Custom SVG components for platforms not available in Lucide
const TiktokIcon = {
    name: 'TiktokIcon',
    props: {
        class: { type: String, default: '' },
        size: { type: [Number, String], default: 24 },
    },
    setup(props: { class?: string; size?: number | string }) {
        const size = typeof props.size === 'number' ? props.size : 24;
        return () => h('svg', {
            class: props.class || 'h-5 w-5',
            width: size,
            height: size,
            viewBox: '0 0 24 24',
            fill: 'currentColor',
        }, [
            h('path', {
                d: 'M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13 9.25 9.25 0 0 1-1.56-.36v-3.5a6.41 6.41 0 0 0 1.58.21 2.93 2.93 0 0 1 1.79-2.79v3.48a6.33 6.33 0 0 0-1.57-.2 2.92 2.92 0 1 0 0 5.84c.5 0 1-.13 1.43-.39a2.9 2.9 0 0 0 1.55-2.55V6.27a4.93 4.93 0 0 0 3.55 1.55v-3.13z'
            })
        ]);
    },
};

const ThreadsIcon = {
    name: 'ThreadsIcon',
    props: {
        class: { type: String, default: '' },
        size: { type: [Number, String], default: 24 },
    },
    setup(props: { class?: string; size?: number | string }) {
        const size = typeof props.size === 'number' ? props.size : 24;
        return () => h('svg', {
            class: props.class || 'h-5 w-5',
            width: size,
            height: size,
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
        }, [
            h('path', {
                d: 'M12 12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5z'
            }),
            h('path', {
                d: 'M12 7v5m0 5c2.761 0 5-2.239 5-5H17c0 1.657-1.343 3-3 3s-3-1.343-3-3H7c0 2.761 2.239 5 5 5z'
            })
        ]);
    },
};

const AppleMusicIcon = {
    name: 'AppleMusicIcon',
    props: {
        class: { type: String, default: '' },
        size: { type: [Number, String], default: 24 },
    },
    setup(props: { class?: string; size?: number | string }) {
        const size = typeof props.size === 'number' ? props.size : 24;
        return () => h('svg', {
            class: props.class || 'h-5 w-5',
            width: size,
            height: size,
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
        }, [
            h('path', { d: 'M12 2v20' }),
            h('path', { d: 'M2 12h20' }),
            h('circle', { cx: '12', cy: '12', r: '10' })
        ]);
    },
};

// Additional custom icons for platforms not in Lucide
const PinterestIcon = {
    name: 'PinterestIcon',
    props: {
        class: { type: String, default: '' },
        size: { type: [Number, String], default: 24 },
    },
    setup(props: { class?: string; size?: number | string }) {
        const size = typeof props.size === 'number' ? props.size : 24;
        return () => h('svg', {
            class: props.class || 'h-5 w-5',
            width: size,
            height: size,
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
        }, [
            h('path', {
                d: 'M12 2C6.48 2 2 6.48 2 12c0 4.84 3.05 8.97 7.35 10.55-.1-.92-.19-2.33.04-3.34.21-1.2 1.34-8.04 1.34-8.04s-.34-.68-.34-1.69c0-1.58.92-2.76 2.06-2.76.97 0 1.44.73 1.44 1.6 0 .98-.62 2.45-.94 3.81-.27 1.14.57 2.07 1.7 2.07 2.04 0 3.61-2.15 3.61-5.25 0-2.75-1.97-4.67-4.78-4.67-3.25 0-5.16 2.44-5.16 4.96 0 .97.37 2.01.84 2.63.09.11.1.21.07.32l-.34 1.36c-.05.19-.16.23-.37.14-1.38-.64-2.24-2.65-2.24-4.27 0-3.48 2.53-6.68 7.3-6.68 3.83 0 6.81 2.73 6.81 6.38 0 3.81-2.4 6.87-5.73 6.87-1.12 0-2.18-.58-2.54-1.27l-.69 2.62c-.25.96-.93 2.16-1.38 2.89 1.04.32 2.14.49 3.28.49 5.52 0 10-4.48 10-10S17.52 2 12 2z'
            })
        ]);
    },
};

const SnapchatIcon = {
    name: 'SnapchatIcon',
    props: {
        class: { type: String, default: '' },
        size: { type: [Number, String], default: 24 },
    },
    setup(props: { class?: string; size?: number | string }) {
        const size = typeof props.size === 'number' ? props.size : 24;
        return () => h('svg', {
            class: props.class || 'h-5 w-5',
            width: size,
            height: size,
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
        }, [
            h('path', {
                d: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z'
            }),
            h('path', {
                d: 'M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z'
            })
        ]);
    },
};

const BehanceIcon = {
    name: 'BehanceIcon',
    props: {
        class: { type: String, default: '' },
        size: { type: [Number, String], default: 24 },
    },
    setup(props: { class?: string; size?: number | string }) {
        const size = typeof props.size === 'number' ? props.size : 24;
        return () => h('svg', {
            class: props.class || 'h-5 w-5',
            width: size,
            height: size,
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
        }, [
            h('path', {
                d: 'M22 7h-4M22 11h-7M8 10a2 2 0 1 1 0-4 2 2 0 0 1 0 4z'
            }),
            h('path', {
                d: 'M14 7h-2a3 3 0 0 0-3 3v0a3 3 0 0 0 3 3h2M14 7v4a3 3 0 0 1-3 3'
            }),
            h('path', {
                d: 'M2 7h6v4H2z'
            })
        ]);
    },
};

const SoundcloudIcon = {
    name: 'SoundcloudIcon',
    props: {
        class: { type: String, default: '' },
        size: { type: [Number, String], default: 24 },
    },
    setup(props: { class?: string; size?: number | string }) {
        const size = typeof props.size === 'number' ? props.size : 24;
        return () => h('svg', {
            class: props.class || 'h-5 w-5',
            width: size,
            height: size,
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
        }, [
            h('path', {
                d: 'M2 13a2 2 0 0 0 0 4h2a2 2 0 0 0 0-4z'
            }),
            h('path', {
                d: 'M7 11a5 5 0 0 0 0 10h2a5 5 0 0 0 0-10z'
            }),
            h('path', {
                d: 'M12 9a7 7 0 0 0 0 14h2a7 7 0 0 0 0-14z'
            }),
            h('path', {
                d: 'M17 7a9 9 0 0 0 0 18h2a9 9 0 0 0 0-18z'
            })
        ]);
    },
};

const DiscordIcon = {
    name: 'DiscordIcon',
    props: {
        class: { type: String, default: '' },
        size: { type: [Number, String], default: 24 },
    },
    setup(props: { class?: string; size?: number | string }) {
        const size = typeof props.size === 'number' ? props.size : 24;
        return () => h('svg', {
            class: props.class || 'h-5 w-5',
            width: size,
            height: size,
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
        }, [
            h('path', {
                d: 'M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z'
            })
        ]);
    },
};

const props = defineProps<{
    profile: any;
    platforms: Record<string, { label: string; icon: string; color: string }>;
    maxLinks: number;
}>();

const swal = useSwal();
const showAddModal = ref(false);
const isUnmounting = ref(false);

const form = useForm({
    platform: '',
    url: '',
    label: '',
});

const canAddMore = computed(() => (props.profile.social_links?.length || 0) < props.maxLinks);

// Close modals before unmounting to prevent Vue DOM errors
onBeforeUnmount(async () => {
    isUnmounting.value = true;
    showAddModal.value = false;
    await nextTick();
});

const platformIcons: Record<string, any> = {
    facebook: Facebook,
    instagram: Instagram,
    twitter: Twitter,
    tiktok: TiktokIcon,
    linkedin: Linkedin,
    youtube: Youtube,
    github: Github,
    whatsapp: MessageCircle,
    telegram: Send,
    pinterest: PinterestIcon,
    snapchat: SnapchatIcon,
    threads: ThreadsIcon,
    dribbble: Dribbble,
    behance: BehanceIcon,
    spotify: Music,
    apple_music: AppleMusicIcon,
    soundcloud: SoundcloudIcon,
    twitch: Twitch,
    discord: DiscordIcon,
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
        <Dialog v-model:open="showAddModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-md">
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





