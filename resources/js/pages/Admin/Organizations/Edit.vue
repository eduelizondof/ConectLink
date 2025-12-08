<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { ref, computed, onMounted, watch } from 'vue';
import {
    Building2,
    User,
    Users,
    Palette,
    Package,
    Link2,
    Bell,
    Save,
    Eye,
    ExternalLink,
    Settings,
} from 'lucide-vue-next';
import { RadioCard } from '@/components/ui/radio-card';
import { ImageUpload } from '@/components/ui/image-upload';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import ProfilePreview from './partials/ProfilePreview.vue';
import ProfileEditor from './partials/ProfileEditor.vue';
import DesignEditor from './partials/DesignEditor.vue';
import ProductsEditor from './partials/ProductsEditor.vue';
import SocialLinksEditor from './partials/SocialLinksEditor.vue';
import CustomLinksEditor from './partials/CustomLinksEditor.vue';
import AlertsEditor from './partials/AlertsEditor.vue';

interface Organization {
    id: number;
    name: string;
    slug: string;
    logo?: string;
    type: string;
    description?: string;
    is_active: boolean;
    profiles: any[];
    productCategories: any[];
    products: any[];
}

const props = defineProps<{
    organization: Organization;
    limits: {
        max_profiles: number;
        max_products: number;
        max_custom_links: number;
        max_social_links: number;
        max_alerts: number;
        can_upload_images: boolean;
        can_remove_branding: boolean;
    };
    socialPlatforms: Record<string, { label: string; icon: string; color: string }>;
}>();

const page = usePage();
const swal = useSwal();

// Active tab
const activeTab = ref('general');

// Selected profile for editing
const selectedProfileId = ref<number | null>(
    props.organization.profiles.find(p => p.is_primary)?.id || props.organization.profiles[0]?.id || null
);

const selectedProfile = computed(() => 
    props.organization.profiles.find(p => p.id === selectedProfileId.value)
);

// Organization form
const orgForm = useForm({
    name: props.organization.name,
    slug: props.organization.slug,
    type: props.organization.type,
    description: props.organization.description || '',
    logo: null as File | null,
    is_active: props.organization.is_active,
});

function saveOrganization() {
    orgForm.put(`/admin/organizations/${props.organization.id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => swal.success('¡Cambios guardados!'),
    });
}

// Show flash messages
onMounted(() => {
    const flash = page.props.flash as { success?: string; error?: string } | undefined;
    if (flash?.success) swal.success(flash.success);
    if (flash?.error) swal.error(flash.error);
});

// Watch for changes in profiles to update selected
watch(() => props.organization.profiles, (profiles) => {
    if (!selectedProfileId.value && profiles.length > 0) {
        selectedProfileId.value = profiles.find(p => p.is_primary)?.id || profiles[0].id;
    }
});
</script>

<template>
    <Head :title="`Editar ${organization.name}`" />

    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/admin' },
        { title: 'Organizaciones', href: '/admin/organizations' },
        { title: organization.name, href: `/admin/organizations/${organization.id}/edit` },
    ]">
        <div class="flex-1 p-4 md:p-6">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500/20 to-cyan-600/20">
                        <img
                            v-if="organization.logo"
                            :src="`/storage/${organization.logo}`"
                            :alt="organization.name"
                            class="h-full w-full rounded-xl object-cover"
                        />
                        <Building2 v-else class="h-7 w-7 text-cyan-600" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">{{ organization.name }}</h1>
                        <a
                            :href="`/${organization.slug}`"
                            target="_blank"
                            class="flex items-center gap-1 text-sm text-muted-foreground hover:text-primary"
                        >
                            conectlink.cnva.mx/{{ organization.slug }}
                            <ExternalLink class="h-3 w-3" />
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        :href="`/${organization.slug}`"
                        target="_blank"
                        class="flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted"
                    >
                        <Eye class="h-4 w-4" />
                        Ver Perfil
                    </a>
                </div>
            </div>

            <!-- Main Content with Tabs -->
            <Tabs v-model="activeTab" class="space-y-6">
                <TabsList class="w-full justify-start overflow-x-auto">
                    <TabsTrigger value="general" class="gap-2">
                        <Settings class="h-4 w-4" />
                        <span class="hidden sm:inline">General</span>
                    </TabsTrigger>
                    <TabsTrigger value="profiles" class="gap-2">
                        <Users class="h-4 w-4" />
                        <span class="hidden sm:inline">Perfiles</span>
                        <span class="ml-1 rounded-full bg-muted px-1.5 py-0.5 text-xs">
                            {{ organization.profiles.length }}
                        </span>
                    </TabsTrigger>
                    <TabsTrigger value="design" class="gap-2">
                        <Palette class="h-4 w-4" />
                        <span class="hidden sm:inline">Diseño</span>
                    </TabsTrigger>
                    <TabsTrigger value="links" class="gap-2">
                        <Link2 class="h-4 w-4" />
                        <span class="hidden sm:inline">Links</span>
                    </TabsTrigger>
                    <TabsTrigger value="products" class="gap-2">
                        <Package class="h-4 w-4" />
                        <span class="hidden sm:inline">Productos</span>
                        <span class="ml-1 rounded-full bg-muted px-1.5 py-0.5 text-xs">
                            {{ organization.products.length }}
                        </span>
                    </TabsTrigger>
                    <TabsTrigger value="alerts" class="gap-2">
                        <Bell class="h-4 w-4" />
                        <span class="hidden sm:inline">Alertas</span>
                    </TabsTrigger>
                </TabsList>

                <!-- General Tab -->
                <TabsContent value="general" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <!-- Form -->
                        <div class="space-y-6 rounded-xl border bg-card p-6">
                            <h2 class="font-semibold">Información General</h2>

                            <form @submit.prevent="saveOrganization" class="space-y-6">
                                <!-- Logo -->
                                <ImageUpload
                                    v-model="orgForm.logo"
                                    :current-image="organization.logo"
                                    label="Logo"
                                    aspect-ratio="square"
                                    :max-size="2"
                                />

                                <!-- Name -->
                                <div class="space-y-2">
                                    <Label for="name">Nombre</Label>
                                    <Input
                                        id="name"
                                        v-model="orgForm.name"
                                        type="text"
                                        required
                                    />
                                </div>

                                <!-- Slug -->
                                <div class="space-y-2">
                                    <Label for="slug">URL</Label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-muted-foreground">conectlink.cnva.mx/</span>
                                        <Input
                                            id="slug"
                                            v-model="orgForm.slug"
                                            type="text"
                                            class="font-mono"
                                            required
                                        />
                                    </div>
                                </div>

                                <!-- Type -->
                                <div class="space-y-3">
                                    <Label>Tipo</Label>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <RadioCard
                                            v-model="orgForm.type"
                                            value="business"
                                            label="Empresa"
                                            :icon="Building2"
                                        />
                                        <RadioCard
                                            v-model="orgForm.type"
                                            value="personal"
                                            label="Personal"
                                            :icon="User"
                                        />
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <Label for="description">Descripción</Label>
                                    <textarea
                                        id="description"
                                        v-model="orgForm.description"
                                        rows="3"
                                        class="w-full rounded-lg border bg-background px-4 py-3 text-sm"
                                        maxlength="500"
                                    />
                                </div>

                                <!-- Active -->
                                <div class="flex items-center gap-3">
                                    <Checkbox
                                        id="is_active"
                                        :checked="orgForm.is_active"
                                        @update:checked="orgForm.is_active = $event"
                                    />
                                    <Label for="is_active" class="cursor-pointer">
                                        Organización activa (visible públicamente)
                                    </Label>
                                </div>

                                <Button type="submit" :disabled="orgForm.processing" class="w-full gap-2">
                                    <Save class="h-4 w-4" />
                                    Guardar Cambios
                                </Button>
                            </form>
                        </div>

                        <!-- Stats & Info -->
                        <div class="space-y-6">
                            <div class="rounded-xl border bg-card p-6">
                                <h2 class="font-semibold mb-4">Resumen</h2>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-lg bg-muted p-4 text-center">
                                        <p class="text-2xl font-bold">{{ organization.profiles.length }}</p>
                                        <p class="text-sm text-muted-foreground">Perfiles</p>
                                    </div>
                                    <div class="rounded-lg bg-muted p-4 text-center">
                                        <p class="text-2xl font-bold">{{ organization.products.length }}</p>
                                        <p class="text-sm text-muted-foreground">Productos</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border bg-card p-6">
                                <h2 class="font-semibold mb-4">Límites de tu Plan</h2>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Perfiles</span>
                                        <span>{{ organization.profiles.length }}/{{ limits.max_profiles }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Productos</span>
                                        <span>{{ organization.products.length }}/{{ limits.max_products }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Links personalizados</span>
                                        <span>{{ limits.max_custom_links }} por perfil</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Redes sociales</span>
                                        <span>{{ limits.max_social_links }} por perfil</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </TabsContent>

                <!-- Profiles Tab -->
                <TabsContent value="profiles">
                    <ProfileEditor
                        :organization="organization"
                        :limits="limits"
                        :selected-profile-id="selectedProfileId"
                        @select-profile="selectedProfileId = $event"
                    />
                </TabsContent>

                <!-- Design Tab -->
                <TabsContent value="design">
                    <DesignEditor
                        :organization="organization"
                        :selected-profile-id="selectedProfileId"
                        @select-profile="selectedProfileId = $event"
                    />
                </TabsContent>

                <!-- Links Tab -->
                <TabsContent value="links">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="space-y-6">
                            <!-- Profile Selector -->
                            <div class="flex items-center gap-4">
                                <Label>Editando perfil:</Label>
                                <select
                                    v-model="selectedProfileId"
                                    class="rounded-lg border bg-background px-3 py-2 text-sm"
                                >
                                    <option v-for="profile in organization.profiles" :key="profile.id" :value="profile.id">
                                        {{ profile.name }} {{ profile.is_primary ? '(Principal)' : '' }}
                                    </option>
                                </select>
                            </div>

                            <!-- Social Links -->
                            <SocialLinksEditor
                                v-if="selectedProfile"
                                :profile="selectedProfile"
                                :platforms="socialPlatforms"
                                :max-links="limits.max_social_links"
                            />

                            <!-- Custom Links -->
                            <CustomLinksEditor
                                v-if="selectedProfile"
                                :profile="selectedProfile"
                                :max-links="limits.max_custom_links"
                            />
                        </div>

                        <!-- Preview -->
                        <div class="space-y-4">
                            <h2 class="font-semibold">Vista Previa</h2>
                            <ProfilePreview
                                v-if="selectedProfile"
                                :profile="selectedProfile"
                                :organization="organization"
                            />
                        </div>
                    </div>
                </TabsContent>

                <!-- Products Tab -->
                <TabsContent value="products">
                    <ProductsEditor
                        :organization="organization"
                        :limits="limits"
                    />
                </TabsContent>

                <!-- Alerts Tab -->
                <TabsContent value="alerts">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="space-y-6">
                            <!-- Profile Selector -->
                            <div class="flex items-center gap-4">
                                <Label>Editando perfil:</Label>
                                <select
                                    v-model="selectedProfileId"
                                    class="rounded-lg border bg-background px-3 py-2 text-sm"
                                >
                                    <option v-for="profile in organization.profiles" :key="profile.id" :value="profile.id">
                                        {{ profile.name }} {{ profile.is_primary ? '(Principal)' : '' }}
                                    </option>
                                </select>
                            </div>

                            <AlertsEditor
                                v-if="selectedProfile"
                                :profile="selectedProfile"
                                :max-alerts="limits.max_alerts"
                            />
                        </div>

                        <!-- Preview -->
                        <div class="space-y-4">
                            <h2 class="font-semibold">Vista Previa</h2>
                            <ProfilePreview
                                v-if="selectedProfile"
                                :profile="selectedProfile"
                                :organization="organization"
                            />
                        </div>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>





