<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { onMounted, ref } from 'vue';
import {
    Building2,
    Plus,
    Users,
    Package,
    ExternalLink,
    MoreVertical,
    Pencil,
    Trash2,
    Eye,
    AlertCircle,
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Organization {
    id: number;
    name: string;
    slug: string;
    logo?: string;
    type: string;
    is_active: boolean;
    profiles_count: number;
    products_count: number;
}

const props = defineProps<{
    organizations: Organization[];
    canCreate: boolean;
    limits: {
        max: number;
        current: number;
    };
}>();

const page = usePage();
const swal = useSwal();
const isLoaded = ref(false);

async function deleteOrganization(org: Organization) {
    const confirmed = await swal.confirmDelete(org.name);
    if (confirmed) {
        router.delete(`/admin/organizations/${org.id}`, {
            preserveScroll: true,
        });
    }
}

onMounted(() => {
    setTimeout(() => isLoaded.value = true, 100);

    const flash = page.props.flash as { success?: string; error?: string } | undefined;
    if (flash?.success) swal.success(flash.success);
    if (flash?.error) swal.error(flash.error);
});
</script>

<template>
    <Head title="Mis Organizaciones" />

    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/admin' },
        { title: 'Organizaciones', href: '/admin/organizations' },
    ]">
        <div class="flex-1 space-y-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Mis Organizaciones</h1>
                    <p class="text-muted-foreground">
                        {{ limits.current }} de {{ limits.max }} organizaciones
                    </p>
                </div>

                <Link
                    v-if="canCreate"
                    href="/admin/organizations/create"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    <Plus class="h-4 w-4" />
                    Nueva Organización
                </Link>
            </div>

            <!-- Limit Warning -->
            <div
                v-if="!canCreate"
                class="flex items-center gap-4 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/50"
            >
                <AlertCircle class="h-5 w-5 text-amber-600" />
                <div class="flex-1">
                    <p class="font-medium text-amber-800 dark:text-amber-200">
                        Has alcanzado el límite de organizaciones
                    </p>
                    <p class="text-sm text-amber-600 dark:text-amber-400">
                        Mejora tu plan para crear más organizaciones.
                    </p>
                </div>
                <Link
                    href="/admin/plans"
                    class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
                >
                    Ver Planes
                </Link>
            </div>

            <!-- Empty State -->
            <div
                v-if="organizations.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed bg-muted/50 p-16 text-center"
            >
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-cyan-100 dark:bg-cyan-900">
                    <Building2 class="h-10 w-10 text-cyan-600 dark:text-cyan-400" />
                </div>
                <h3 class="mt-6 text-xl font-semibold">No tienes organizaciones</h3>
                <p class="mt-2 max-w-md text-muted-foreground">
                    Crea tu primera organización para comenzar a construir tu presencia digital.
                </p>
                <Link
                    href="/admin/organizations/create"
                    class="mt-6 inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 font-medium text-primary-foreground hover:bg-primary/90"
                >
                    <Plus class="h-5 w-5" />
                    Crear mi primera organización
                </Link>
            </div>

            <!-- Organizations Grid -->
            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(org, index) in organizations"
                    :key="org.id"
                    class="group relative rounded-xl border bg-card transition-all hover:border-primary/50 hover:shadow-lg"
                    :class="{ 'opacity-0 translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                    :style="{ transitionDelay: `${index * 0.05}s` }"
                >
                    <!-- Header with Logo -->
                    <div class="relative h-32 overflow-hidden rounded-t-xl bg-gradient-to-br from-cyan-500/20 to-cyan-600/20">
                        <img
                            v-if="org.logo"
                            :src="`/storage/${org.logo}`"
                            :alt="org.name"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full items-center justify-center">
                            <Building2 class="h-16 w-16 text-cyan-500/50" />
                        </div>

                        <!-- Status Badge -->
                        <span
                            class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-xs font-medium"
                            :class="org.is_active
                                ? 'bg-emerald-500 text-white'
                                : 'bg-gray-500 text-white'"
                        >
                            {{ org.is_active ? 'Activo' : 'Inactivo' }}
                        </span>

                        <!-- Actions Menu -->
                        <DropdownMenu>
                            <DropdownMenuTrigger class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-gray-600 opacity-0 transition-opacity group-hover:opacity-100 hover:bg-white">
                                <MoreVertical class="h-4 w-4" />
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem as-child>
                                    <Link :href="`/admin/organizations/${org.id}/edit`" class="flex items-center gap-2">
                                        <Pencil class="h-4 w-4" />
                                        Editar
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child>
                                    <a :href="`/${org.slug}`" target="_blank" class="flex items-center gap-2">
                                        <Eye class="h-4 w-4" />
                                        Ver Perfil
                                    </a>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    class="flex items-center gap-2 text-destructive focus:text-destructive"
                                    @click="deleteOrganization(org)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    Eliminar
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <!-- Content -->
                    <Link :href="`/admin/organizations/${org.id}/edit`" class="block p-4">
                        <h3 class="font-semibold truncate">{{ org.name }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground truncate">
                            conectlink.cnva.mx/{{ org.slug }}
                        </p>

                        <!-- Stats -->
                        <div class="mt-4 flex items-center gap-4 text-sm text-muted-foreground">
                            <span class="flex items-center gap-1.5">
                                <Users class="h-4 w-4" />
                                {{ org.profiles_count }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <Package class="h-4 w-4" />
                                {{ org.products_count }}
                            </span>
                            <span class="ml-auto flex items-center gap-1">
                                <ExternalLink class="h-3.5 w-3.5" />
                            </span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


