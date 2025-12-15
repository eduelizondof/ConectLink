<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import {
    Download,
    X,
    ChevronRight,
    ShoppingBag,
    Tag,
    Star,
    Eye,
    Share2,
    Package,
    Sparkles,
    AlertCircle,
    Info,
    CheckCircle,
    Megaphone,
} from 'lucide-vue-next';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
    faFacebook,
    faInstagram,
    faTwitter,
    faLinkedin,
    faYoutube,
    faGithub,
    faTiktok,
    faWhatsapp,
    faTelegram,
    faPinterest,
    faSnapchat,
    faThreads,
    faDribbble,
    faBehance,
    faSpotify,
    faSoundcloud,
    faTwitch,
    faDiscord,
    faApple,
} from '@fortawesome/free-brands-svg-icons';
import { faGlobe, faEnvelope, faPhone, faLink } from '@fortawesome/free-solid-svg-icons';
import { GlowingBorder, ProfileParticles } from '@/components/effects';

// Props from controller
interface SocialLink {
    id: number;
    platform: string;
    url: string;
    label: string;
    icon: string;
    brand_color: string;
}

interface CustomLink {
    id: number;
    title: string;
    url: string;
    description?: string;
    icon?: string;
    thumbnail?: string;
    button_color?: string;
    text_color?: string;
    is_highlighted: boolean;
}

interface FloatingAlert {
    id: number;
    title?: string;
    message: string;
    type: string;
    icon: string;
    link_url?: string;
    link_text?: string;
    position: string;
    animation: string;
    background_color: string;
    text_color?: string;
    is_dismissible: boolean;
    show_on_load: boolean;
    delay_seconds: number;
}

interface ProfileSettings {
    background_type: string;
    background_color: string;
    background_gradient_start?: string;
    background_gradient_end?: string;
    background_gradient_direction: string;
    background_image?: string;
    background_overlay_opacity: number;
    background_animated_media?: string;
    background_animated_media_type?: 'image' | 'gif' | 'video';
    background_animated_overlay_opacity?: number;
    background_pattern?: string;
    background_pattern_opacity?: number;
    primary_color: string;
    secondary_color: string;
    text_color: string;
    text_secondary_color: string;
    card_style: string;
    card_background_color: string;
    card_border_radius: string;
    card_shadow: boolean;
    card_border_color?: string;
    card_glow_enabled?: boolean;
    card_glow_color?: string;
    card_glow_color_secondary?: string;
    card_glow_variant?: 'default' | 'cyan' | 'purple' | 'rainbow' | 'primary';
    font_family: string;
    font_size: string;
    animation_entrance: string;
    animation_hover: string;
    animation_delay: number;
    show_particles?: boolean;
    particles_style?: string;
    particles_color?: string;
    layout_style: string;
    show_profile_photo: boolean;
    photo_style: string;
    photo_size: string;
    social_style: string;
    social_size: string;
    social_colored: boolean;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    short_description?: string;
    description?: string;
    price?: number;
    sale_price?: number;
    currency: string;
    image?: string;
    is_featured: boolean;
    is_available: boolean;
    category?: { id: number; name: string; slug: string };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    products_count: number;
}

interface Profile {
    id: number;
    name: string;
    slug?: string;
    photo?: string;
    job_title?: string;
    slogan?: string;
    bio?: string;
    is_primary: boolean;
    views_count: number;
    settings?: ProfileSettings;
    social_links: SocialLink[];
    custom_links: CustomLink[];
    floating_alerts: FloatingAlert[];
    vcard?: { is_active: boolean; full_name: string };
}

interface Organization {
    id: number;
    name: string;
    slug: string;
    logo?: string;
    logo_url?: string;
    type: string;
    description?: string;
}

const props = defineProps<{
    organization: Organization;
    profile: Profile;
    products: Product[];
    categories: Category[];
}>();

// State
const isLoaded = ref(false);
const dismissedAlerts = ref<number[]>([]);
const visibleAlerts = ref<number[]>([]);
const selectedCategory = ref<number | null>(null);
const showProductModal = ref(false);
const selectedProduct = ref<Product | null>(null);
const showShareMenu = ref(false);
const currentUrl = ref('');

// Computed
const settings = computed(() => props.profile.settings || getDefaultSettings());

const backgroundStyle = computed(() => {
    const s = settings.value;
    if (s.background_type === 'gradient') {
        return {
            background: `linear-gradient(${getGradientDirection(s.background_gradient_direction)}, ${s.background_gradient_start}, ${s.background_gradient_end})`,
        };
    } else if (s.background_type === 'image' && s.background_image) {
        return {
            backgroundImage: `url(${s.background_image})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
        };
    } else if (s.background_type === 'animated' && s.background_animated_media) {
        // For animated backgrounds, return empty style as we'll use a video/img element
        return {};
    }
    return { backgroundColor: s.background_color };
});

const cardClasses = computed(() => {
    const s = settings.value;
    const classes = ['transition-all duration-300'];

    // Border radius
    const radiusMap: Record<string, string> = {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
        '2xl': 'rounded-2xl',
        full: 'rounded-3xl',
    };
    classes.push(radiusMap[s.card_border_radius] || 'rounded-lg');

    // Shadow
    if (s.card_shadow) {
        classes.push('shadow-lg');
    }

    return classes.join(' ');
});

const cardStyle = computed(() => {
    const s = settings.value;
    const style: Record<string, string> = {
        backgroundColor: s.card_background_color,
    };

    if (s.card_style === 'glass') {
        style.backdropFilter = 'blur(20px)';
        style.WebkitBackdropFilter = 'blur(20px)';
    }

    if (s.card_border_color) {
        style.border = `1px solid ${s.card_border_color}`;
    }

    return style;
});

const photoClasses = computed(() => {
    const s = settings.value;
    const classes = ['overflow-hidden border-4', 'transition-transform duration-300'];

    // Style
    const styleMap: Record<string, string> = {
        circle: 'rounded-full',
        rounded: 'rounded-2xl',
        square: 'rounded-none',
    };
    classes.push(styleMap[s.photo_style] || 'rounded-full');

    // Size
    const sizeMap: Record<string, string> = {
        sm: 'w-20 h-20',
        md: 'w-28 h-28',
        lg: 'w-36 h-36',
        xl: 'w-44 h-44',
    };
    classes.push(sizeMap[s.photo_size] || 'w-36 h-36');

    return classes.join(' ');
});

const filteredProducts = computed(() => {
    if (!selectedCategory.value) return props.products;
    return props.products.filter((p) => p.category?.id === selectedCategory.value);
});

const activeAlerts = computed(() => {
    return props.profile.floating_alerts.filter(
        (alert) => visibleAlerts.value.includes(alert.id) && !dismissedAlerts.value.includes(alert.id)
    );
});

// Methods
function getDefaultSettings(): ProfileSettings {
    return {
        background_type: 'solid',
        background_color: '#ffffff',
        background_gradient_direction: 'to-b',
        background_overlay_opacity: 0,
        background_pattern: 'none',
        background_pattern_opacity: 10,
        primary_color: '#3b82f6',
        secondary_color: '#8b5cf6',
        text_color: '#1f2937',
        text_secondary_color: '#6b7280',
        card_style: 'solid',
        card_background_color: '#ffffff',
        card_border_radius: 'lg',
        card_shadow: true,
        card_glow_enabled: false,
        card_glow_variant: 'primary',
        font_family: 'Inter',
        font_size: 'base',
        animation_entrance: 'fade',
        animation_hover: 'lift',
        animation_delay: 100,
        show_particles: false,
        particles_style: 'dots',
        layout_style: 'centered',
        show_profile_photo: true,
        photo_style: 'circle',
        photo_size: 'lg',
        social_style: 'icons',
        social_size: 'md',
        social_colored: true,
    };
}

// Computed for glow settings
const glowSettings = computed(() => ({
    enabled: settings.value.card_glow_enabled || false,
    color: settings.value.card_glow_color || settings.value.primary_color,
    colorSecondary: settings.value.card_glow_color_secondary || settings.value.secondary_color,
    variant: settings.value.card_glow_variant || 'primary',
    borderRadius: cardBorderRadiusValue.value,
}));

const cardBorderRadiusValue = computed(() => {
    const radiusMap: Record<string, string> = {
        none: '0',
        sm: '0.25rem',
        md: '0.375rem',
        lg: '0.5rem',
        xl: '0.75rem',
        '2xl': '1rem',
        full: '1.5rem',
    };
    return radiusMap[settings.value.card_border_radius] || '0.5rem';
});

function getGradientDirection(direction: string): string {
    const map: Record<string, string> = {
        'to-b': '180deg',
        'to-r': '90deg',
        'to-br': '135deg',
        'to-bl': '225deg',
    };
    return map[direction] || '180deg';
}

function getSocialIcon(platform: string) {
    const icons: Record<string, any> = {
        facebook: faFacebook,
        instagram: faInstagram,
        twitter: faTwitter,
        linkedin: faLinkedin,
        youtube: faYoutube,
        github: faGithub,
        tiktok: faTiktok,
        whatsapp: faWhatsapp,
        telegram: faTelegram,
        pinterest: faPinterest,
        snapchat: faSnapchat,
        threads: faThreads,
        dribbble: faDribbble,
        behance: faBehance,
        spotify: faSpotify,
        apple_music: faApple,
        soundcloud: faSoundcloud,
        twitch: faTwitch,
        discord: faDiscord,
        website: faGlobe,
        email: faEnvelope,
        phone: faPhone,
        other: faLink,
    };
    return icons[platform] || faLink;
}

function getAlertIcon(type: string) {
    const icons: Record<string, any> = {
        info: Info,
        promo: Tag,
        warning: AlertCircle,
        success: CheckCircle,
        announcement: Megaphone,
    };
    return icons[type] || Info;
}

function getAlertPositionClasses(position: string): string {
    const positions: Record<string, string> = {
        top: 'top-4 left-1/2 -translate-x-1/2',
        bottom: 'bottom-4 left-1/2 -translate-x-1/2',
        'top-left': 'top-4 left-4',
        'top-right': 'top-4 right-4',
        'bottom-left': 'bottom-4 left-4',
        'bottom-right': 'bottom-4 right-4',
    };
    return positions[position] || 'bottom-4 right-4';
}

function getAnimationClasses(animation: string): string {
    const animations: Record<string, string> = {
        bounce: 'animate-bounce',
        pulse: 'animate-pulse',
        shake: 'animate-shake',
        slide: 'animate-slide-in',
        none: '',
    };
    return animations[animation] || '';
}

function dismissAlert(alertId: number) {
    dismissedAlerts.value.push(alertId);
}

function openProductModal(product: Product) {
    selectedProduct.value = product;
    showProductModal.value = true;
}

function closeProductModal() {
    showProductModal.value = false;
    selectedProduct.value = null;
}

function formatPrice(price: number, currency: string): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: currency,
    }).format(price);
}

function trackClick(linkId: number) {
    fetch(`/api/links/${linkId}/click`, { method: 'POST' }).catch(() => {});
}

async function downloadVcard() {
    const url = props.profile.is_primary
        ? `/${props.organization.slug}/vcard`
        : `/${props.organization.slug}/${props.profile.slug}/vcard`;

    window.location.href = url;
}

async function shareProfile() {
    const url = currentUrl.value;
    const title = `${props.profile.name} - ${props.organization.name}`;

    if (navigator.share) {
        try {
            await navigator.share({ title, url });
        } catch {
            showShareMenu.value = true;
        }
    } else {
        showShareMenu.value = true;
    }
}

function copyToClipboard() {
    navigator.clipboard.writeText(currentUrl.value);
    showShareMenu.value = false;
}

// Lifecycle
onMounted(() => {
    // Store current URL for template access
    currentUrl.value = window.location.href;

    setTimeout(() => {
        isLoaded.value = true;
    }, 100);

    // Show alerts with delay
    props.profile.floating_alerts.forEach((alert) => {
        if (alert.show_on_load) {
            setTimeout(() => {
                visibleAlerts.value.push(alert.id);
            }, alert.delay_seconds * 1000);
        }
    });
});
</script>

<template>
    <Head :title="`${profile.name} | ${organization.name}`">
        <meta name="description" :content="profile.bio || profile.slogan || organization.description" />
        <meta property="og:title" :content="`${profile.name} | ${organization.name}`" />
        <meta property="og:description" :content="profile.slogan || profile.bio" />
        <meta property="og:image" :content="profile.photo || organization.logo_url" />
    </Head>

    <!-- Main Container -->
    <div class="min-h-screen" :style="backgroundStyle">
        <!-- Animated Background (Video/GIF/Image) -->
        <div
            v-if="settings.background_type === 'animated' && settings.background_animated_media"
            class="fixed inset-0 z-0 overflow-hidden"
        >
            <!-- Video Background -->
            <video
                v-if="settings.background_animated_media_type === 'video'"
                :src="`/storage/${settings.background_animated_media}`"
                autoplay
                loop
                muted
                playsinline
                class="absolute inset-0 w-full h-full object-cover"
            />
            <!-- GIF/Image Background -->
            <img
                v-else
                :src="`/storage/${settings.background_animated_media}`"
                alt="Background"
                class="absolute inset-0 w-full h-full object-cover"
            />
            <!-- Overlay for animated backgrounds -->
            <div
                v-if="(settings.background_animated_overlay_opacity || 0) > 0"
                class="absolute inset-0 bg-black pointer-events-none"
                :style="{ opacity: (settings.background_animated_overlay_opacity || 0) / 100 }"
            />
        </div>

        <!-- Background Pattern Overlay -->
        <div
            v-if="settings.background_pattern && settings.background_pattern !== 'none'"
            class="fixed inset-0 pointer-events-none z-0"
            :class="[`bg-pattern-${settings.background_pattern}`]"
            :style="{ opacity: (settings.background_pattern_opacity || 10) / 100 }"
        />

        <!-- Background Overlay (for static images) -->
        <div
            v-if="settings.background_type === 'image' && settings.background_overlay_opacity > 0"
            class="fixed inset-0 bg-black pointer-events-none z-0"
            :style="{ opacity: settings.background_overlay_opacity / 100 }"
        />

        <!-- Particles Effect -->
        <ProfileParticles
            v-if="settings.show_particles"
            :style="settings.particles_style as any"
            :color="settings.particles_color"
            :primary-color="settings.primary_color"
            :secondary-color="settings.secondary_color"
            :particle-count="40"
        />

        <!-- Content -->
        <main class="relative z-10 mx-auto max-w-lg px-4 py-8 pb-32">
            <!-- Profile Header -->
            <div
                class="text-center mb-8"
                :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                style="transition: all 0.6s ease-out"
            >
                <!-- Photo -->
                <div v-if="settings.show_profile_photo && (profile.photo || organization.logo_url)" class="flex justify-center mb-4">
                    <div :class="photoClasses" :style="{ borderColor: settings.primary_color }">
                        <img
                            :src="profile.photo || organization.logo_url"
                            :alt="profile.name"
                            class="w-full h-full object-cover"
                        />
                    </div>
                </div>

                <!-- Name & Title -->
                <h1 class="text-2xl font-bold mb-1" :style="{ color: settings.text_color, fontFamily: settings.font_family }">
                    {{ profile.name }}
                </h1>
                <p v-if="profile.job_title" class="text-sm mb-2" :style="{ color: settings.text_secondary_color }">
                    {{ profile.job_title }}
                </p>

                <!-- Slogan -->
                <p v-if="profile.slogan" class="text-lg font-medium mb-4" :style="{ color: settings.text_color }">
                    {{ profile.slogan }}
                </p>

                <!-- Bio -->
                <p v-if="profile.bio" class="text-sm leading-relaxed max-w-md mx-auto" :style="{ color: settings.text_secondary_color }">
                    {{ profile.bio }}
                </p>
            </div>

            <!-- Social Links -->
            <div
                v-if="profile.social_links.length > 0"
                class="flex flex-wrap justify-center gap-3 mb-8"
                :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                style="transition: all 0.6s ease-out 0.1s"
            >
                <!-- Icons Style -->
                <template v-if="settings.social_style === 'icons'">
                    <a
                        v-for="link in profile.social_links"
                        :key="link.id"
                        :href="link.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center justify-center rounded-full transition-all duration-300 hover:scale-110"
                        :class="{
                            'w-10 h-10': settings.social_size === 'sm',
                            'w-12 h-12': settings.social_size === 'md',
                            'w-14 h-14': settings.social_size === 'lg',
                        }"
                        :style="{
                            backgroundColor: settings.social_colored ? link.brand_color : settings.primary_color,
                            color: '#ffffff',
                        }"
                        :title="link.label"
                    >
                        <font-awesome-icon :icon="getSocialIcon(link.platform)" class="w-5 h-5" />
                    </a>
                </template>

                <!-- Buttons Style -->
                <template v-else-if="settings.social_style === 'buttons'">
                    <a
                        v-for="link in profile.social_links"
                        :key="link.id"
                        :href="link.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-300 hover:scale-105"
                        :style="{
                            backgroundColor: settings.social_colored ? link.brand_color : settings.primary_color,
                            color: '#ffffff',
                        }"
                    >
                        <font-awesome-icon :icon="getSocialIcon(link.platform)" class="w-4 h-4" />
                        <span class="text-sm font-medium">{{ link.label }}</span>
                    </a>
                </template>

                <!-- Pills Style -->
                <template v-else>
                    <a
                        v-for="link in profile.social_links"
                        :key="link.id"
                        :href="link.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-4 py-2 rounded-full transition-all duration-300 hover:scale-105"
                        :style="{
                            backgroundColor: settings.social_colored ? `${link.brand_color}20` : `${settings.primary_color}20`,
                            color: settings.social_colored ? link.brand_color : settings.primary_color,
                            border: `1px solid ${settings.social_colored ? link.brand_color : settings.primary_color}`,
                        }"
                    >
                        <font-awesome-icon :icon="getSocialIcon(link.platform)" class="w-4 h-4" />
                        <span class="text-sm font-medium">{{ link.label }}</span>
                    </a>
                </template>
            </div>

            <!-- Custom Links -->
            <div
                v-if="profile.custom_links.length > 0"
                class="space-y-3 mb-8"
                :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                style="transition: all 0.6s ease-out 0.2s"
            >
                <component
                    :is="glowSettings.enabled ? GlowingBorder : 'div'"
                    v-for="(link, index) in profile.custom_links"
                    :key="link.id"
                    :color="glowSettings.color"
                    :color-secondary="glowSettings.colorSecondary"
                    :variant="glowSettings.variant"
                    :border-radius="glowSettings.borderRadius"
                    :border-width="2"
                    :duration="3"
                    :style="{ animationDelay: `${index * settings.animation_delay}ms` }"
                >
                    <a
                        :href="link.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block w-full p-4 text-center transition-all duration-300"
                        :class="[
                            cardClasses,
                            settings.animation_hover === 'lift' ? 'hover:-translate-y-1' : '',
                            settings.animation_hover === 'glow' && !glowSettings.enabled ? 'hover:shadow-2xl' : '',
                            settings.animation_hover === 'pulse' ? 'hover:animate-pulse' : '',
                            link.is_highlighted && !glowSettings.enabled ? 'ring-2' : '',
                        ]"
                        :style="{
                            ...cardStyle,
                            backgroundColor: link.button_color || cardStyle.backgroundColor,
                            color: link.text_color || settings.text_color,
                            '--tw-ring-color': settings.primary_color,
                        }"
                        @click="trackClick(link.id)"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 text-left flex-1">
                                <div
                                    v-if="link.thumbnail"
                                    class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0"
                                >
                                    <img :src="link.thumbnail" :alt="link.title" class="w-full h-full object-cover" />
                                </div>
                                <div>
                                    <p class="font-semibold">{{ link.title }}</p>
                                    <p v-if="link.description" class="text-sm opacity-70">{{ link.description }}</p>
                                </div>
                            </div>
                            <ChevronRight class="w-5 h-5 opacity-50" />
                        </div>
                    </a>
                </component>
            </div>

            <!-- Products Catalog -->
            <div
                v-if="products.length > 0"
                id="catalogo"
                class="mb-8"
                :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                style="transition: all 0.6s ease-out 0.3s"
            >
                <!-- Section Header -->
                <div class="flex items-center gap-2 mb-4">
                    <ShoppingBag class="w-5 h-5" :style="{ color: settings.primary_color }" />
                    <h2 class="text-lg font-bold" :style="{ color: settings.text_color }">Catálogo</h2>
                </div>

                <!-- Category Filter -->
                <div v-if="categories.length > 1" class="flex flex-wrap gap-2 mb-4">
                    <button
                        class="px-3 py-1.5 text-sm rounded-full transition-all duration-300"
                        :class="selectedCategory === null ? 'font-semibold' : 'opacity-70'"
                        :style="{
                            backgroundColor: selectedCategory === null ? settings.primary_color : 'transparent',
                            color: selectedCategory === null ? '#ffffff' : settings.text_color,
                            border: `1px solid ${settings.primary_color}`,
                        }"
                        @click="selectedCategory = null"
                    >
                        Todos
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        class="px-3 py-1.5 text-sm rounded-full transition-all duration-300"
                        :class="selectedCategory === cat.id ? 'font-semibold' : 'opacity-70'"
                        :style="{
                            backgroundColor: selectedCategory === cat.id ? settings.primary_color : 'transparent',
                            color: selectedCategory === cat.id ? '#ffffff' : settings.text_color,
                            border: `1px solid ${settings.primary_color}`,
                        }"
                        @click="selectedCategory = cat.id"
                    >
                        {{ cat.name }} ({{ cat.products_count }})
                    </button>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <component
                        :is="glowSettings.enabled ? GlowingBorder : 'div'"
                        v-for="product in filteredProducts"
                        :key="product.id"
                        :color="glowSettings.color"
                        :color-secondary="glowSettings.colorSecondary"
                        :variant="glowSettings.variant"
                        :border-radius="glowSettings.borderRadius"
                        :border-width="2"
                        :duration="3"
                    >
                        <button
                            class="text-left transition-all duration-300 w-full"
                            :class="[
                                cardClasses,
                                settings.animation_hover === 'lift' ? 'hover:-translate-y-1' : '',
                            ]"
                            :style="cardStyle"
                            @click="openProductModal(product)"
                        >
                            <!-- Product Image -->
                            <div class="aspect-square rounded-t-lg overflow-hidden bg-gray-100 relative">
                                <img
                                    v-if="product.image"
                                    :src="product.image"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <Package class="w-12 h-12 text-gray-300" />
                                </div>
                                <!-- Featured Badge -->
                                <div
                                    v-if="product.is_featured"
                                    class="absolute top-2 left-2 px-2 py-0.5 text-xs font-semibold rounded-full"
                                    :style="{ backgroundColor: settings.secondary_color, color: '#ffffff' }"
                                >
                                    <Star class="w-3 h-3 inline mr-1" />
                                    Destacado
                                </div>
                                <!-- Sale Badge -->
                                <div
                                    v-if="product.sale_price && product.sale_price < (product.price || 0)"
                                    class="absolute top-2 right-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-red-500 text-white"
                                >
                                    Oferta
                                </div>
                            </div>
                            <!-- Product Info -->
                            <div class="p-3">
                                <h3 class="font-semibold text-sm line-clamp-2 mb-1" :style="{ color: settings.text_color }">
                                    {{ product.name }}
                                </h3>
                                <div v-if="product.price" class="flex items-center gap-2">
                                    <span
                                        v-if="product.sale_price && product.sale_price < product.price"
                                        class="text-sm font-bold"
                                        :style="{ color: settings.primary_color }"
                                    >
                                        {{ formatPrice(product.sale_price, product.currency) }}
                                    </span>
                                    <span
                                        :class="product.sale_price && product.sale_price < product.price ? 'line-through text-xs opacity-50' : 'text-sm font-bold'"
                                        :style="{ color: product.sale_price && product.sale_price < product.price ? settings.text_secondary_color : settings.primary_color }"
                                    >
                                        {{ formatPrice(product.price, product.currency) }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    </component>
                </div>
            </div>

            <!-- Action Buttons -->
            <div
                class="flex gap-3 justify-center"
                :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                style="transition: all 0.6s ease-out 0.4s"
            >
                <!-- Download vCard -->
                <button
                    v-if="profile.vcard?.is_active"
                    @click="downloadVcard"
                    class="flex items-center gap-2 px-5 py-3 rounded-xl font-semibold transition-all duration-300 hover:scale-105"
                    :style="{ backgroundColor: settings.primary_color, color: '#ffffff' }"
                >
                    <Download class="w-5 h-5" />
                    Guardar Contacto
                </button>

                <!-- Share -->
                <button
                    @click="shareProfile"
                    class="flex items-center gap-2 px-5 py-3 rounded-xl font-semibold transition-all duration-300 hover:scale-105"
                    :style="{
                        backgroundColor: `${settings.primary_color}20`,
                        color: settings.primary_color,
                        border: `1px solid ${settings.primary_color}`,
                    }"
                >
                    <Share2 class="w-5 h-5" />
                    Compartir
                </button>
            </div>

            <!-- Views Counter -->
            <div class="text-center mt-8 opacity-50" :style="{ color: settings.text_secondary_color }">
                <div class="flex items-center justify-center gap-1 text-xs">
                    <Eye class="w-3 h-3" />
                    <span>{{ profile.views_count.toLocaleString() }} visitas</span>
                </div>
            </div>

            <!-- Powered By -->
            <div class="text-center mt-4">
                <a
                    href="/"
                    class="inline-flex items-center gap-1 text-xs opacity-40 hover:opacity-70 transition-opacity"
                    :style="{ color: settings.text_secondary_color }"
                >
                    <Sparkles class="w-3 h-3" />
                    Creado con ConectLink
                </a>
            </div>
        </main>

        <!-- Floating Alerts -->
        <Teleport to="body">
            <component
                v-for="alert in activeAlerts"
                :key="alert.id"
                :is="alert.link_url ? 'a' : 'div'"
                :href="alert.link_url || undefined"
                :target="alert.link_url ? '_blank' : undefined"
                :rel="alert.link_url ? 'noopener noreferrer' : undefined"
                class="fixed z-50 max-w-sm mx-4 block"
                :class="[
                    getAlertPositionClasses(alert.position),
                    getAnimationClasses(alert.animation),
                    alert.link_url ? 'cursor-pointer' : '',
                ]"
            >
                <div
                    class="rounded-xl p-4 shadow-2xl flex items-start gap-3 transition-transform"
                    :class="alert.link_url ? 'hover:scale-[1.02]' : ''"
                    :style="{
                        backgroundColor: alert.background_color,
                        color: alert.text_color || '#ffffff',
                    }"
                >
                    <component :is="getAlertIcon(alert.type)" class="w-5 h-5 flex-shrink-0 mt-0.5" />
                    <div class="flex-1 min-w-0">
                        <p v-if="alert.title" class="font-semibold text-sm mb-1">{{ alert.title }}</p>
                        <p class="text-sm opacity-90">{{ alert.message }}</p>
                        <span
                            v-if="alert.link_url && alert.link_text"
                            class="inline-flex items-center gap-1 text-sm font-semibold mt-2 hover:underline"
                        >
                            {{ alert.link_text }}
                            <ChevronRight class="w-4 h-4" />
                        </span>
                    </div>
                    <button
                        v-if="alert.is_dismissible"
                        @click.stop.prevent="dismissAlert(alert.id)"
                        class="p-1 rounded-full hover:bg-white/20 transition-colors"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </component>
        </Teleport>

        <!-- Product Modal -->
        <Teleport to="body">
            <div
                v-if="showProductModal && selectedProduct"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
                @click.self="closeProductModal"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeProductModal" />

                <!-- Modal Content -->
                <div
                    class="relative bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto animate-slide-up"
                >
                    <!-- Close Button -->
                    <button
                        @click="closeProductModal"
                        class="absolute top-4 right-4 z-10 p-2 rounded-full bg-black/10 hover:bg-black/20 transition-colors"
                    >
                        <X class="w-5 h-5" />
                    </button>

                    <!-- Product Image -->
                    <div class="aspect-video w-full overflow-hidden rounded-t-3xl sm:rounded-t-3xl bg-gray-100">
                        <img
                            v-if="selectedProduct.image"
                            :src="selectedProduct.image"
                            :alt="selectedProduct.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <Package class="w-20 h-20 text-gray-300" />
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="p-6">
                        <!-- Category Badge -->
                        <div v-if="selectedProduct.category" class="mb-2">
                            <span
                                class="inline-block px-3 py-1 text-xs font-medium rounded-full"
                                :style="{
                                    backgroundColor: `${settings.primary_color}20`,
                                    color: settings.primary_color,
                                }"
                            >
                                {{ selectedProduct.category.name }}
                            </span>
                        </div>

                        <!-- Name -->
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ selectedProduct.name }}</h2>

                        <!-- Price -->
                        <div v-if="selectedProduct.price" class="flex items-center gap-3 mb-4">
                            <span
                                v-if="selectedProduct.sale_price && selectedProduct.sale_price < selectedProduct.price"
                                class="text-2xl font-bold"
                                :style="{ color: settings.primary_color }"
                            >
                                {{ formatPrice(selectedProduct.sale_price, selectedProduct.currency) }}
                            </span>
                            <span
                                :class="selectedProduct.sale_price && selectedProduct.sale_price < selectedProduct.price ? 'line-through text-lg text-gray-400' : 'text-2xl font-bold'"
                                :style="{
                                    color: selectedProduct.sale_price && selectedProduct.sale_price < selectedProduct.price ? '' : settings.primary_color,
                                }"
                            >
                                {{ formatPrice(selectedProduct.price, selectedProduct.currency) }}
                            </span>
                            <span
                                v-if="selectedProduct.sale_price && selectedProduct.sale_price < selectedProduct.price"
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-500 text-white"
                            >
                                -{{ Math.round(((selectedProduct.price - selectedProduct.sale_price) / selectedProduct.price) * 100) }}%
                            </span>
                        </div>

                        <!-- Description -->
                        <p v-if="selectedProduct.short_description" class="text-gray-600 mb-4">
                            {{ selectedProduct.short_description }}
                        </p>
                        <div v-if="selectedProduct.description" class="prose prose-sm text-gray-600 mb-6">
                            {{ selectedProduct.description }}
                        </div>

                        <!-- Availability -->
                        <div class="flex items-center gap-2 mb-6">
                            <div
                                class="w-2 h-2 rounded-full"
                                :class="selectedProduct.is_available ? 'bg-green-500' : 'bg-red-500'"
                            />
                            <span class="text-sm" :class="selectedProduct.is_available ? 'text-green-600' : 'text-red-600'">
                                {{ selectedProduct.is_available ? 'Disponible' : 'No disponible' }}
                            </span>
                        </div>

                        <!-- CTA Button -->
                        <a
                            v-if="profile.social_links.find((l) => l.platform === 'whatsapp')"
                            :href="`${profile.social_links.find((l) => l.platform === 'whatsapp')?.url}?text=${encodeURIComponent(`Hola, me interesa: ${selectedProduct.name}`)}`"
                            target="_blank"
                            class="flex items-center justify-center gap-2 w-full py-4 rounded-xl font-semibold text-white transition-all duration-300 hover:scale-[1.02]"
                            :style="{ backgroundColor: '#25D366' }"
                        >
                            <font-awesome-icon :icon="faWhatsapp" class="w-5 h-5" />
                            Consultar por WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Share Menu -->
        <Teleport to="body">
            <div
                v-if="showShareMenu"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
                @click.self="showShareMenu = false"
            >
                <div class="absolute inset-0 bg-black/60" @click="showShareMenu = false" />
                <div class="relative bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-6 animate-slide-up">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Compartir perfil</h3>
                    <div class="space-y-3">
                        <button
                            @click="copyToClipboard"
                            class="flex items-center gap-3 w-full p-3 rounded-xl bg-gray-100 hover:bg-gray-200 transition-colors"
                        >
                            <font-awesome-icon :icon="faLink" class="w-5 h-5 text-gray-600" />
                            <span class="font-medium text-gray-700">Copiar enlace</span>
                        </button>
                        <a
                            :href="`https://wa.me/?text=${encodeURIComponent(`Mira el perfil de ${profile.name}: ${currentUrl}`)}`"
                            target="_blank"
                            class="flex items-center gap-3 w-full p-3 rounded-xl bg-green-50 hover:bg-green-100 transition-colors"
                        >
                            <font-awesome-icon :icon="faWhatsapp" class="w-5 h-5 text-green-600" />
                            <span class="font-medium text-green-700">Compartir en WhatsApp</span>
                        </a>
                    </div>
                    <button
                        @click="showShareMenu = false"
                        class="w-full mt-4 py-3 text-gray-500 font-medium"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
/* Animations */
@keyframes slide-up {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}

@keyframes slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slide-in 0.5s ease-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.animate-shake {
    animation: shake 0.5s ease-in-out infinite;
}

/* Soft bounce animation - more subtle than default */
@keyframes soft-bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

.animate-bounce {
    animation: soft-bounce 2s ease-in-out infinite;
}

/* Line clamp */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Background Patterns */
.bg-pattern-dots {
    background-image: radial-gradient(circle, currentColor 1px, transparent 1px);
    background-size: 20px 20px;
}

.bg-pattern-grid {
    background-image: 
        linear-gradient(to right, currentColor 1px, transparent 1px),
        linear-gradient(to bottom, currentColor 1px, transparent 1px);
    background-size: 30px 30px;
}

.bg-pattern-waves {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='currentColor' fill-opacity='1' d='M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,144C672,139,768,181,864,197.3C960,213,1056,203,1152,181.3C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
    background-size: cover;
    background-position: bottom;
}

.bg-pattern-noise {
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
}
</style>

