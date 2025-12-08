<script setup lang="ts">
import { ref, computed, onBeforeUnmount, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import {
    Plus,
    Pencil,
    Trash2,
    User,
    Crown,
    Save,
} from 'lucide-vue-next';
import { ImageUpload } from '@/components/ui/image-upload';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';

const props = defineProps<{
    organization: any;
    limits: any;
    selectedProfileId: number | null;
}>();

const emit = defineEmits<{
    'select-profile': [id: number];
}>();

const swal = useSwal();
const showCreateModal = ref(false);
const editingProfile = ref<any>(null);
const isUnmounting = ref(false);

const canCreateProfile = computed(() => props.organization.profiles.length < props.limits.max_profiles);

// Close modals before unmounting to prevent Vue DOM errors
onBeforeUnmount(async () => {
    isUnmounting.value = true;
    showCreateModal.value = false;
    editingProfile.value = null;
    await nextTick();
});

// Create form
const createForm = useForm({
    name: '',
    slug: '',
    job_title: '',
    slogan: '',
    bio: '',
    photo: null as File | null,
});

// Edit form
const editForm = useForm({
    name: '',
    slug: '',
    job_title: '',
    slogan: '',
    bio: '',
    photo: null as File | null,
    is_active: true,
});

function openCreateModal() {
    createForm.reset();
    showCreateModal.value = true;
}

function openEditModal(profile: any) {
    editingProfile.value = profile;
    editForm.name = profile.name;
    editForm.slug = profile.slug || '';
    editForm.job_title = profile.job_title || '';
    editForm.slogan = profile.slogan || '';
    editForm.bio = profile.bio || '';
    editForm.is_active = profile.is_active;
}

function closeEditModal() {
    editingProfile.value = null;
}

function submitCreate() {
    createForm.post(`/admin/organizations/${props.organization.id}/profiles`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            swal.success('¡Perfil creado!');
        },
    });
}

function submitEdit() {
    if (!editingProfile.value) return;

    // If photo is a File, use POST with _method spoofing
    // Otherwise, use PUT normally
    if (editForm.photo instanceof File) {
        editForm
            .transform((data) => ({
                ...data,
                _method: 'PUT',
            }))
            .post(`/admin/profiles/${editingProfile.value.id}`, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    closeEditModal();
                    swal.success('¡Perfil actualizado!');
                },
            });
    } else {
        editForm.transform((data) => {
            // eslint-disable-next-line @typescript-eslint/no-unused-vars
            const { photo, ...rest } = data;
            return rest;
        }).put(`/admin/profiles/${editingProfile.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeEditModal();
                swal.success('¡Perfil actualizado!');
            },
        });
    }
}

async function deleteProfile(profile: any) {
    if (profile.is_primary) {
        swal.error('No puedes eliminar el perfil principal');
        return;
    }

    const confirmed = await swal.confirmDelete(profile.name);
    if (confirmed) {
        router.delete(`/admin/profiles/${profile.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">Perfiles</h2>
                <p class="text-sm text-muted-foreground">
                    {{ organization.profiles.length }}/{{ limits.max_profiles }} perfiles
                </p>
            </div>
            <Button
                v-if="canCreateProfile"
                @click="openCreateModal"
                class="gap-2"
            >
                <Plus class="h-4 w-4" />
                Nuevo Perfil
            </Button>
        </div>

        <!-- Profiles Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="profile in organization.profiles"
                :key="profile.id"
                class="group relative rounded-xl border bg-card p-4 transition-all hover:border-primary/50 hover:shadow-md cursor-pointer"
                :class="{ 'ring-2 ring-primary': selectedProfileId === profile.id }"
                @click="emit('select-profile', profile.id)"
            >
                <!-- Primary Badge -->
                <div
                    v-if="profile.is_primary"
                    class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-white"
                >
                    <Crown class="h-3 w-3" />
                </div>

                <div class="flex items-start gap-4">
                    <!-- Avatar -->
                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-full bg-muted">
                        <img
                            v-if="profile.photo_url"
                            :src="profile.photo_url"
                            :alt="profile.name"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <User class="h-6 w-6 text-muted-foreground" />
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold truncate">{{ profile.name }}</h3>
                        <p v-if="profile.job_title" class="text-sm text-muted-foreground truncate">
                            {{ profile.job_title }}
                        </p>
                        <p v-if="!profile.is_primary" class="text-xs text-muted-foreground mt-1">
                            /{{ organization.slug }}/{{ profile.slug }}
                        </p>
                        <span
                            class="mt-2 inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="profile.is_active
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300'
                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                        >
                            {{ profile.is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-4 flex gap-2">
                    <Button size="sm" variant="outline" class="flex-1 gap-1" @click.stop="openEditModal(profile)">
                        <Pencil class="h-3 w-3" />
                        Editar
                    </Button>
                    <Button
                        v-if="!profile.is_primary"
                        size="sm"
                        variant="outline"
                        class="text-destructive hover:bg-destructive hover:text-destructive-foreground"
                        @click.stop="deleteProfile(profile)"
                    >
                        <Trash2 class="h-3 w-3" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Create Profile Modal -->
        <Dialog v-model:open="showCreateModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nuevo Perfil</DialogTitle>
                    <DialogDescription>
                        Crea un nuevo perfil para un empleado o departamento.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitCreate" class="space-y-4">
                    <ImageUpload
                        v-model="createForm.photo"
                        label="Foto"
                        aspect-ratio="square"
                        :max-size="2"
                    />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="create-name">Nombre *</Label>
                            <Input
                                id="create-name"
                                v-model="createForm.name"
                                required
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="create-slug">URL</Label>
                            <Input
                                id="create-slug"
                                v-model="createForm.slug"
                                placeholder="juan-perez"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="create-job_title">Cargo</Label>
                        <Input
                            id="create-job_title"
                            v-model="createForm.job_title"
                            placeholder="Asesor de Ventas"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="create-slogan">Slogan</Label>
                        <Input
                            id="create-slogan"
                            v-model="createForm.slogan"
                            placeholder="Tu aliado en..."
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="create-bio">Biografía</Label>
                        <textarea
                            id="create-bio"
                            v-model="createForm.bio"
                            rows="3"
                            class="w-full rounded-lg border bg-background px-4 py-3 text-sm"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Button type="button" variant="outline" @click="showCreateModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="createForm.processing">
                            Crear Perfil
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Profile Modal -->
        <Dialog :open="!!editingProfile && !isUnmounting" @update:open="closeEditModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Editar Perfil</DialogTitle>
                </DialogHeader>

                <form v-if="editingProfile" @submit.prevent="submitEdit" class="space-y-4">
                    <ImageUpload
                        v-model="editForm.photo"
                        :current-image="editingProfile.photo_url"
                        label="Foto"
                        aspect-ratio="square"
                        :max-size="2"
                    />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="edit-name">Nombre *</Label>
                            <Input
                                id="edit-name"
                                v-model="editForm.name"
                                required
                            />
                        </div>
                        <div v-if="!editingProfile.is_primary" class="space-y-2">
                            <Label for="edit-slug">URL</Label>
                            <Input
                                id="edit-slug"
                                v-model="editForm.slug"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-job_title">Cargo</Label>
                        <Input
                            id="edit-job_title"
                            v-model="editForm.job_title"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-slogan">Slogan</Label>
                        <Input
                            id="edit-slogan"
                            v-model="editForm.slogan"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-bio">Biografía</Label>
                        <textarea
                            id="edit-bio"
                            v-model="editForm.bio"
                            rows="3"
                            class="w-full rounded-lg border bg-background px-4 py-3 text-sm"
                        />
                    </div>

                    <div class="flex items-center gap-3">
                        <Checkbox
                            id="edit-is_active"
                            :checked="editForm.is_active"
                            @update:checked="editForm.is_active = $event"
                        />
                        <Label for="edit-is_active" class="cursor-pointer">
                            Perfil activo
                        </Label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Button type="button" variant="outline" @click="closeEditModal">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="editForm.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            Guardar
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>





