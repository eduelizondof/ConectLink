<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useSwal } from '@/composables/useSwal';
import {
    Check,
    X,
    Crown,
    Sparkles,
    Building2,
    Users,
    Package,
    Link2,
    Bell,
    Globe,
    Palette,
    BarChart3,
    Image,
} from 'lucide-vue-next';

interface Plan {
    id: number;
    name: string;
    slug: string;
    description?: string;
    prices: {
        monthly: number;
        quarterly: number;
        semiannual: number;
        annual: number;
    };
    savings: {
        quarterly: number;
        semiannual: number;
        annual: number;
    };
    currency: string;
    features?: string[];
    limits: {
        organizations: number;
        profiles: number;
        products: number;
        custom_links: number;
        social_links: number;
        alerts: number;
    };
    capabilities: {
        custom_domain: boolean;
        remove_branding: boolean;
        analytics: boolean;
        upload_images: boolean;
    };
    is_featured: boolean;
}

const props = defineProps<{
    plans: Plan[];
    currentSubscription: {
        plan_id: number;
        plan_name: string;
        billing_cycle: string;
        status: string;
        ends_at: string;
        days_remaining: number;
    } | null;
}>();

const page = usePage();
const swal = useSwal();
const isLoaded = ref(false);

// Billing cycle selection
const billingCycle = ref<'monthly' | 'quarterly' | 'semiannual' | 'annual'>('annual');

const billingOptions = [
    { value: 'monthly', label: 'Mensual', months: 1 },
    { value: 'quarterly', label: '3 Meses', months: 3 },
    { value: 'semiannual', label: '6 Meses', months: 6 },
    { value: 'annual', label: 'Anual', months: 12 },
];

function getPrice(plan: Plan, cycle: string): number {
    return plan.prices[cycle as keyof typeof plan.prices] || plan.prices.monthly;
}

function getSavings(plan: Plan, cycle: string): number {
    return plan.savings[cycle as keyof typeof plan.savings] || 0;
}

function formatPrice(price: number, currency: string): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
    }).format(price);
}

function selectPlan(plan: Plan) {
    swal.info(`Para activar el plan "${plan.name}", contacta a soporte.`);
}

onMounted(() => {
    setTimeout(() => isLoaded.value = true, 100);

    const flash = page.props.flash as { success?: string; error?: string } | undefined;
    if (flash?.success) swal.success(flash.success);
    if (flash?.error) swal.error(flash.error);
});
</script>

<template>
    <Head title="Planes y Precios" />

    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/admin' },
        { title: 'Planes', href: '/admin/plans' },
    ]">
        <div class="flex-1 p-4 md:p-6">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-2 mb-4">
                    <Sparkles class="h-4 w-4 text-primary" />
                    <span class="text-sm font-medium text-primary">Planes ConectLink</span>
                </div>
                <h1 class="text-3xl font-bold">Elige tu plan perfecto</h1>
                <p class="mt-2 text-muted-foreground max-w-xl mx-auto">
                    Potencia tu presencia digital con las herramientas que necesitas.
                    Todos los planes incluyen actualizaciones gratuitas.
                </p>
            </div>

            <!-- Current Subscription Alert -->
            <div
                v-if="currentSubscription"
                class="mx-auto max-w-3xl mb-8 rounded-xl border-2 border-primary/50 bg-primary/5 p-4 flex items-center gap-4"
                :class="{ 'opacity-0 translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground">
                    <Crown class="h-6 w-6" />
                </div>
                <div class="flex-1">
                    <p class="font-semibold">
                        Tu plan actual: <span class="text-primary">{{ currentSubscription.plan_name }}</span>
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ currentSubscription.status === 'active' ? 'Activo' : 'Inactivo' }}
                        <span v-if="currentSubscription.ends_at">
                            • Vence el {{ currentSubscription.ends_at }}
                            ({{ currentSubscription.days_remaining }} días restantes)
                        </span>
                    </p>
                </div>
            </div>

            <!-- Billing Cycle Selector -->
            <div
                class="flex justify-center mb-8"
                :class="{ 'opacity-0 translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                style="transition: all 0.5s ease-out 0.1s"
            >
                <div class="inline-flex rounded-xl bg-muted p-1">
                    <button
                        v-for="option in billingOptions"
                        :key="option.value"
                        class="relative px-4 py-2 rounded-lg text-sm font-medium transition-all"
                        :class="billingCycle === option.value
                            ? 'bg-background text-foreground shadow-sm'
                            : 'text-muted-foreground hover:text-foreground'"
                        @click="billingCycle = option.value as any"
                    >
                        {{ option.label }}
                        <span
                            v-if="option.value === 'annual'"
                            class="absolute -top-2 -right-2 rounded-full bg-emerald-500 px-1.5 py-0.5 text-[10px] font-bold text-white"
                        >
                            -17%
                        </span>
                    </button>
                </div>
            </div>

            <!-- Plans Grid -->
            <div class="grid gap-6 max-w-5xl mx-auto md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(plan, index) in plans"
                    :key="plan.id"
                    class="relative rounded-2xl border-2 bg-card p-6 transition-all hover:shadow-xl"
                    :class="[
                        plan.is_featured ? 'border-primary shadow-lg' : 'border-border',
                        { 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }
                    ]"
                    :style="{ transitionDelay: `${0.15 + index * 0.1}s` }"
                >
                    <!-- Featured Badge -->
                    <div
                        v-if="plan.is_featured"
                        class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-primary px-4 py-1 text-xs font-bold text-primary-foreground"
                    >
                        Más popular
                    </div>

                    <!-- Plan Header -->
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold">{{ plan.name }}</h3>
                        <p v-if="plan.description" class="mt-1 text-sm text-muted-foreground">
                            {{ plan.description }}
                        </p>
                    </div>

                    <!-- Price -->
                    <div class="text-center mb-6">
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-4xl font-bold">
                                {{ formatPrice(getPrice(plan, billingCycle), plan.currency) }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ billingCycle === 'monthly' ? 'por mes' :
                               billingCycle === 'quarterly' ? 'por 3 meses' :
                               billingCycle === 'semiannual' ? 'por 6 meses' : 'por año' }}
                        </p>
                        <p v-if="getSavings(plan, billingCycle) > 0" class="text-sm font-medium text-emerald-600">
                            Ahorras {{ getSavings(plan, billingCycle) }}%
                        </p>
                    </div>

                    <!-- CTA Button -->
                    <button
                        @click="selectPlan(plan)"
                        class="w-full rounded-xl py-3 font-semibold transition-all"
                        :class="plan.is_featured
                            ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                            : 'bg-muted hover:bg-muted/80'"
                    >
                        {{ currentSubscription?.plan_id === plan.id ? 'Plan actual' : 'Seleccionar plan' }}
                    </button>

                    <!-- Limits -->
                    <div class="mt-6 space-y-3">
                        <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">Incluye:</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <Building2 class="h-4 w-4 text-primary" />
                                <span>{{ plan.limits.organizations }} organización{{ plan.limits.organizations > 1 ? 'es' : '' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Users class="h-4 w-4 text-primary" />
                                <span>{{ plan.limits.profiles }} perfiles por org.</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Package class="h-4 w-4 text-primary" />
                                <span>{{ plan.limits.products }} productos</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link2 class="h-4 w-4 text-primary" />
                                <span>{{ plan.limits.custom_links }} links personalizados</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Bell class="h-4 w-4 text-primary" />
                                <span>{{ plan.limits.alerts }} alertas flotantes</span>
                            </div>
                        </div>
                    </div>

                    <!-- Capabilities -->
                    <div class="mt-6 space-y-3 border-t pt-6">
                        <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">Características:</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <component
                                    :is="plan.capabilities.analytics ? Check : X"
                                    class="h-4 w-4"
                                    :class="plan.capabilities.analytics ? 'text-emerald-500' : 'text-muted-foreground'"
                                />
                                <span :class="!plan.capabilities.analytics && 'text-muted-foreground'">Estadísticas</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <component
                                    :is="plan.capabilities.upload_images ? Check : X"
                                    class="h-4 w-4"
                                    :class="plan.capabilities.upload_images ? 'text-emerald-500' : 'text-muted-foreground'"
                                />
                                <span :class="!plan.capabilities.upload_images && 'text-muted-foreground'">Subir imágenes</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <component
                                    :is="plan.capabilities.remove_branding ? Check : X"
                                    class="h-4 w-4"
                                    :class="plan.capabilities.remove_branding ? 'text-emerald-500' : 'text-muted-foreground'"
                                />
                                <span :class="!plan.capabilities.remove_branding && 'text-muted-foreground'">Sin marca ConectLink</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <component
                                    :is="plan.capabilities.custom_domain ? Check : X"
                                    class="h-4 w-4"
                                    :class="plan.capabilities.custom_domain ? 'text-emerald-500' : 'text-muted-foreground'"
                                />
                                <span :class="!plan.capabilities.custom_domain && 'text-muted-foreground'">Dominio personalizado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="mt-12 text-center">
                <p class="text-muted-foreground">
                    ¿Necesitas un plan personalizado?
                    <a href="mailto:soporte@conectlink.cnva.mx" class="font-medium text-primary hover:underline">
                        Contáctanos
                    </a>
                </p>
            </div>
        </div>
    </AppLayout>
</template>


