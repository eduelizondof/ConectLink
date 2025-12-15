<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import type { Profile, Organization, Product, Category, ProductSection } from '@/types/profile';
import { useProfileSettings } from '@/composables/useProfileSettings';
import ProfileBackground from '@/components/profile/ProfileBackground.vue';
import ProfileHeader from '@/components/profile/ProfileHeader.vue';
import SocialLinks from '@/components/profile/SocialLinks.vue';
import CustomLinks from '@/components/profile/CustomLinks.vue';
import ProductsCatalog from '@/components/profile/ProductsCatalog.vue';
import ProductModal from '@/components/profile/ProductModal.vue';
import FloatingAlerts from '@/components/profile/FloatingAlerts.vue';
import ProfileActions from '@/components/profile/ProfileActions.vue';
import ShareMenu from '@/components/profile/ShareMenu.vue';

const props = defineProps<{
    organization: Organization;
    profile: Profile;
    products: Product[];
    categories: Category[];
    sections?: ProductSection[];
}>();

// State
const isLoaded = ref(false);
const dismissedAlerts = ref<number[]>([]);
const visibleAlerts = ref<number[]>([]);
const showProductModal = ref(false);
const selectedProduct = ref<Product | null>(null);
const showShareMenu = ref(false);
const currentUrl = ref('');

// Settings composable
const { settings, backgroundStyle } = useProfileSettings(props.profile.settings);

// Methods
function dismissAlert(alertId: number) {
    dismissedAlerts.value.push(alertId);
}

function openProductModal(product: Product) {
    selectedProduct.value = product;
    showProductModal.value = true;
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

// Watch for modal close to reset selected product
watch(showProductModal, (newValue) => {
    if (!newValue) {
        selectedProduct.value = null;
    }
});

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
        <!-- Background Layer -->
        <ProfileBackground :settings="settings" />

        <!-- Content -->
        <main class="relative z-10 mx-auto max-w-lg px-4 py-8 pb-32">
            <!-- Profile Header -->
            <ProfileHeader
                :profile="profile"
                :organization="organization"
                :settings="settings"
                :is-loaded="isLoaded"
            />

            <!-- Social Links -->
            <SocialLinks
                :links="profile.social_links"
                :settings="settings"
                :is-loaded="isLoaded"
            />

            <!-- Custom Links -->
            <CustomLinks
                :links="profile.custom_links"
                :settings="settings"
                :is-loaded="isLoaded"
            />

            <!-- Products Catalog -->
            <ProductsCatalog
                v-if="(products && products.length > 0) || (sections && sections.length > 0)"
                :products="products || []"
                :categories="categories || []"
                :sections="sections || []"
                :settings="settings"
                :is-loaded="isLoaded"
                @product-selected="openProductModal"
            />

            <!-- Action Buttons -->
            <ProfileActions
                :profile="profile"
                :organization="organization"
                :settings="settings"
                :is-loaded="isLoaded"
                :current-url="currentUrl"
                @download-vcard="downloadVcard"
                @share="shareProfile"
            />
        </main>

        <!-- Floating Alerts -->
        <FloatingAlerts
            :alerts="profile.floating_alerts"
            :visible-alerts="visibleAlerts"
            :dismissed-alerts="dismissedAlerts"
            @dismiss="dismissAlert"
        />

        <!-- Product Modal -->
        <ProductModal
            v-model="showProductModal"
            :product="selectedProduct"
            :profile="profile"
            :settings="settings"
        />

        <!-- Share Menu -->
        <ShareMenu
            v-model="showShareMenu"
            :profile="profile"
            :current-url="currentUrl"
            @copy-url="copyToClipboard"
        />
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

/* Entrance Animations */
@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}

@keyframes slide-down {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-slide-down {
    animation: slide-down 0.6s ease-out;
}

@keyframes scale-in {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-scale-in {
    animation: scale-in 0.6s ease-out;
}

@keyframes bounce-in {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    50% {
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-bounce-in {
    animation: bounce-in 0.6s ease-out;
}
</style>
