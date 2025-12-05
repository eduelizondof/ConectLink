<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { useSwal } from '@/composables/useSwal';
import {
    Building2,
    Users,
    Package,
    Eye,
    Plus,
    ArrowRight,
    Lightbulb,
    Share2,
    BarChart3,
    Bell,
    Crown,
    AlertTriangle,
    ExternalLink,
    Sparkles,
} from 'lucide-vue-next';

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

interface Tip {
    icon: string;
    title: string;
    description: string;
}

const props = defineProps<{
    organizations: Organization[];
    stats: {
        total_organizations: number;
        total_profiles: number;
        total_products: number;
        total_views: number;
    };
    limits: {
        max_organizations: number;
        max_profiles_per_org: number;
        max_products_per_org: number;
        can_access_analytics: boolean;
    };
    subscription: {
        plan_name: string;
        status: string;
        ends_at: string;
        days_remaining: number;
    } | null;
    tips: Tip[];
}>();

const page = usePage();
const swal = useSwal();
const isLoaded = ref(false);

const user = computed(() => page.props.auth.user);
const canCreateOrg = computed(() => props.stats.total_organizations < props.limits.max_organizations);

const statCards = computed(() => [
    {
        label: 'Organizaciones',
        value: props.stats.total_organizations,
        max: props.limits.max_organizations,
        icon: Building2,
        color: 'from-cyan-500 to-cyan-600',
    },
    {
        label: 'Perfiles',
        value: props.stats.total_profiles,
        icon: Users,
        color: 'from-purple-500 to-purple-600',
    },
    {
        label: 'Productos',
        value: props.stats.total_products,
        icon: Package,
        color: 'from-emerald-500 to-emerald-600',
    },
    {
        label: 'Visitas Totales',
        value: props.stats.total_views,
        icon: Eye,
        color: 'from-amber-500 to-amber-600',
    },
]);

const tipIcons: Record<string, any> = {
    lightbulb: Lightbulb,
    share: Share2,
    chart: BarChart3,
    bell: Bell,
};

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 100);

    // Show flash messages
    const flash = page.props.flash as { success?: string; error?: string } | undefined;
    if (flash?.success) {
        swal.success(flash.success);
    }
    if (flash?.error) {
        swal.error(flash.error);
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/admin' }]">
        <div class="flex-1 space-y-6 p-4 md:p-6">
            <!-- Welcome Header -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 p-6 text-white shadow-lg"
                :class="{ 'opacity-0 translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                style="transition: all 0.5s ease-out"
            >
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl" />
                <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-white/10 blur-2xl" />

                <div class="relative">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold md:text-3xl">
                                ¡Hola, {{ user.name }}! 👋
                            </h1>
                            <p class="mt-2 text-cyan-100">
                                Bienvenido a tu panel de ConectLink
                            </p>
                        </div>

                        <!-- Subscription Badge -->
                        <div v-if="subscription" class="hidden md:block">
                            <div
                                class="flex items-center gap-2 rounded-full px-4 py-2"
                                :class="subscription.status === 'active' ? 'bg-white/20' : 'bg-amber-500/80'"
                            >
                                <Crown class="h-4 w-4" />
                                <span class="text-sm font-medium">
                                    {{ subscription.plan_name }}
                                    <span v-if="subscription.days_remaining <= 7" class="ml-1 text-xs">
                                        ({{ subscription.days_remaining }} días)
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Summary -->
                    <div class="mt-6 flex flex-wrap gap-4">
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 backdrop-blur-sm">
                            <Building2 class="h-5 w-5" />
                            <span class="text-sm">
                                {{ stats.total_organizations }}/{{ limits.max_organizations }} organizaciones
                            </span>
                        </div>
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 backdrop-blur-sm">
                            <Eye class="h-5 w-5" />
                            <span class="text-sm">
                                {{ stats.total_views.toLocaleString() }} visitas totales
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert for no subscription -->
            <div
                v-if="!subscription"
                class="flex items-center gap-4 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/50"
                :class="{ 'opacity-0 translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                style="transition: all 0.5s ease-out 0.1s"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900">
                    <AlertTriangle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div class="flex-1">
                    <p class="font-medium text-amber-800 dark:text-amber-200">
                        No tienes una suscripción activa
                    </p>
                    <p class="text-sm text-amber-600 dark:text-amber-400">
                        Activa tu plan para desbloquear todas las funciones.
                    </p>
                </div>
                <Link
                    href="/admin/plans"
                    class="flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
                >
                    Ver Planes
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(stat, index) in statCards"
                    :key="stat.label"
                    class="relative overflow-hidden rounded-xl border bg-card p-5 shadow-sm transition-all hover:shadow-md"
                    :class="{ 'opacity-0 translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                    :style="{ transitionDelay: `${0.1 + index * 0.05}s` }"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">{{ stat.label }}</p>
                            <p class="mt-1 text-3xl font-bold">
                                {{ stat.value.toLocaleString() }}
                                <span v-if="stat.max" class="text-lg font-normal text-muted-foreground">
                                    /{{ stat.max }}
                                </span>
                            </p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br"
                            :class="stat.color"
                        >
                            <component :is="stat.icon" class="h-6 w-6 text-white" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Organizations List -->
                <div
                    class="lg:col-span-2 space-y-4"
                    :class="{ 'opacity-0 translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                    style="transition: all 0.5s ease-out 0.3s"
                >
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Mis Organizaciones</h2>
                        <Link
                            v-if="canCreateOrg"
                            href="/admin/organizations/create"
                            class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            <Plus class="h-4 w-4" />
                            Nueva
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="organizations.length === 0"
                        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed bg-muted/50 p-12 text-center"
                    >
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-cyan-100 dark:bg-cyan-900">
                            <Building2 class="h-8 w-8 text-cyan-600 dark:text-cyan-400" />
                        </div>
                        <h3 class="mt-4 text-lg font-semibold">Crea tu primera organización</h3>
                        <p class="mt-2 max-w-sm text-sm text-muted-foreground">
                            Una organización puede ser tu empresa, negocio o perfil personal.
                            Empieza a construir tu presencia digital.
                        </p>
                        <Link
                            href="/admin/organizations/create"
                            class="mt-6 flex items-center gap-2 rounded-lg bg-primary px-6 py-3 font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            <Sparkles class="h-5 w-5" />
                            Crear Organización
                        </Link>
                    </div>

                    <!-- Organizations Cards -->
                    <div v-else class="space-y-3">
                        <Link
                            v-for="org in organizations"
                            :key="org.id"
                            :href="`/admin/organizations/${org.id}/edit`"
                            class="flex items-center gap-4 rounded-xl border bg-card p-4 transition-all hover:border-primary/50 hover:shadow-md"
                        >
                            <!-- Logo -->
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500/20 to-cyan-600/20 text-cyan-600"
                            >
                                <img
                                    v-if="org.logo"
                                    :src="`/storage/${org.logo}`"
                                    :alt="org.name"
                                    class="h-full w-full rounded-xl object-cover"
                                />
                                <Building2 v-else class="h-6 w-6" />
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold truncate">{{ org.name }}</h3>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="org.is_active
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300'
                                            : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                                    >
                                        {{ org.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground truncate">
                                    conectlink.cnva.mx/{{ org.slug }}
                                </p>
                                <div class="mt-2 flex items-center gap-4 text-xs text-muted-foreground">
                                    <span class="flex items-center gap-1">
                                        <Users class="h-3 w-3" />
                                        {{ org.profiles_count }} perfiles
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Package class="h-3 w-3" />
                                        {{ org.products_count }} productos
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <a
                                    :href="`/${org.slug}`"
                                    target="_blank"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted hover:bg-muted/80"
                                    @click.stop
                                >
                                    <ExternalLink class="h-4 w-4" />
                                </a>
                                <ArrowRight class="h-5 w-5 text-muted-foreground" />
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Tips Sidebar -->
                <div
                    class="space-y-4"
                    :class="{ 'opacity-0 translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                    style="transition: all 0.5s ease-out 0.4s"
                >
                    <h2 class="text-lg font-semibold">Consejos Útiles</h2>

                    <div class="space-y-3">
                        <div
                            v-for="(tip, index) in tips"
                            :key="index"
                            class="rounded-xl border bg-card p-4"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-100 dark:bg-cyan-900">
                                    <component :is="tipIcons[tip.icon]" class="h-5 w-5 text-cyan-600 dark:text-cyan-400" />
                                </div>
                                <div>
                                    <h4 class="font-medium">{{ tip.title }}</h4>
                                    <p class="mt-1 text-sm text-muted-foreground">{{ tip.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="rounded-xl border bg-card p-4">
                        <h3 class="font-medium mb-3">Acceso Rápido</h3>
                        <div class="space-y-2">
                            <Link
                                href="/admin/plans"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-muted"
                            >
                                <Crown class="h-4 w-4 text-amber-500" />
                                Ver Planes
                            </Link>
                            <Link
                                href="/settings/profile"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-muted"
                            >
                                <Users class="h-4 w-4 text-cyan-500" />
                                Mi Cuenta
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


