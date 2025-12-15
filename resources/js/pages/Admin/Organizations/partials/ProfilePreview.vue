<script setup lang="ts">
import { computed } from 'vue';
import { Download, Eye } from 'lucide-vue-next';
import type { Profile, Organization, ProfileSettings, Product, Category, ProductSection } from '@/types/profile';
import { getDefaultSettings, useProfileSettings } from '@/composables/useProfileSettings';
import { useProfileHelpers } from '@/composables/useProfileHelpers';
import ProfileHeader from '@/components/profile/ProfileHeader.vue';
import SocialLinks from '@/components/profile/SocialLinks.vue';
import CustomLinks from '@/components/profile/CustomLinks.vue';
import ProductsCatalog from '@/components/profile/ProductsCatalog.vue';

interface ProfilePreviewProps {
    id: number;
    name: string;
    photo?: string;
    job_title?: string;
    slogan?: string;
    bio?: string;
    settings?: Partial<ProfileSettings>;
    social_links?: any[];
    custom_links?: any[];
}

interface OrganizationPreviewProps {
    name: string;
    logo?: string;
    logo_url?: string;
    products?: any[];
    productCategories?: any[];
    productSections?: any[];
}

const props = defineProps<{
    profile: ProfilePreviewProps;
    organization: OrganizationPreviewProps;
}>();

// Get helper functions
const { getBrandColor } = useProfileHelpers();

// Log organization data to debug structure
console.log('ProfilePreview - Organization data:', {
    organization: props.organization,
    products: props.organization.products,
    productCategories: props.organization.productCategories,
    productSections: props.organization.productSections,
    productsLength: props.organization.products?.length,
    sectionsLength: props.organization.productSections?.length,
    // Check if organization has these properties directly
    hasProductCategories: 'productCategories' in props.organization,
    hasProductSections: 'productSections' in props.organization,
    organizationKeys: Object.keys(props.organization),
});

// Merge settings with defaults - MUST be computed for reactivity
// Use the same approach as ProfilePage but ensure all settings are properly merged
const normalizedSettings = computed<ProfileSettings>(() => {
    const defaults = getDefaultSettings();
    const profileSettings = props.profile.settings || {};
    
    return {
        ...defaults,
        ...profileSettings,
        // Ensure required settings are present
        background_type: profileSettings.background_type || defaults.background_type,
        background_color: profileSettings.background_color || defaults.background_color,
        background_gradient_start: profileSettings.background_gradient_start || defaults.background_gradient_start,
        background_gradient_end: profileSettings.background_gradient_end || defaults.background_gradient_end,
        background_gradient_direction: profileSettings.background_gradient_direction || defaults.background_gradient_direction,
        background_animated_media: profileSettings.background_animated_media,
        background_animated_media_type: profileSettings.background_animated_media_type,
        background_animated_overlay_opacity: profileSettings.background_animated_overlay_opacity ?? defaults.background_animated_overlay_opacity,
        primary_color: profileSettings.primary_color || defaults.primary_color,
        text_color: profileSettings.text_color || defaults.text_color,
        text_secondary_color: profileSettings.text_secondary_color || defaults.text_secondary_color,
        card_style: profileSettings.card_style || defaults.card_style,
        card_background_color: profileSettings.card_background_color || defaults.card_background_color,
        card_border_radius: profileSettings.card_border_radius || defaults.card_border_radius,
        card_shadow: profileSettings.card_shadow ?? defaults.card_shadow,
        card_border_color: profileSettings.card_border_color || defaults.card_border_color,
        card_glow_enabled: profileSettings.card_glow_enabled ?? defaults.card_glow_enabled,
        card_glow_color: profileSettings.card_glow_color || defaults.card_glow_color,
        card_glow_color_secondary: profileSettings.card_glow_color_secondary || defaults.card_glow_color_secondary,
        card_glow_variant: profileSettings.card_glow_variant || defaults.card_glow_variant,
        photo_style: profileSettings.photo_style || defaults.photo_style,
        photo_size: profileSettings.photo_size || 'md', // Smaller for preview
        social_style: profileSettings.social_style || defaults.social_style,
        social_size: profileSettings.social_size || defaults.social_size,
        social_colored: profileSettings.social_colored ?? defaults.social_colored,
        show_profile_photo: profileSettings.show_profile_photo ?? defaults.show_profile_photo,
    };
});

// Use the same composable as ProfilePage for consistency - now accepts computed
const { backgroundStyle } = useProfileSettings(normalizedSettings);

// Normalize profile data to match ProfilePage format
const normalizedProfile = computed<Profile>(() => {
    // Get social links (handle both snake_case and camelCase)
    const socialLinks = props.profile.social_links || (props.profile as any).socialLinks || [];
    
    // Get custom links (handle both snake_case and camelCase)
    const customLinks = props.profile.custom_links || (props.profile as any).customLinks || [];
    
    // Normalize custom links to include sort_order if missing
    const normalizedCustomLinks = customLinks.map((link: any, index: number) => ({
        ...link,
        sort_order: link.sort_order ?? index,
    }));

    return {
        id: props.profile.id,
        name: props.profile.name,
        slug: undefined,
        photo: props.profile.photo ? `/storage/${props.profile.photo}` : undefined,
        job_title: props.profile.job_title,
        slogan: props.profile.slogan,
        bio: props.profile.bio,
        is_primary: false,
        views_count: 0,
        settings: normalizedSettings.value,
        social_links: socialLinks.map((link: any) => ({
            id: link.id,
            platform: link.platform,
            url: link.url,
            label: link.label || link.display_label || link.platform,
            icon: link.icon,
            brand_color: link.brand_color || getBrandColor(link.platform),
        })),
        custom_links: normalizedCustomLinks.map((link: any) => ({
            id: link.id,
            title: link.title,
            url: link.url,
            description: link.description,
            icon: link.icon,
            thumbnail: link.thumbnail,
            image: link.image,
            image_position: link.image_position,
            image_shape: link.image_shape,
            button_color: link.button_color,
            text_color: link.text_color,
            is_highlighted: link.is_highlighted || false,
        })),
        floating_alerts: [], // Alerts not shown in preview (they use Teleport to body)
    };
});

// Normalize organization data
const normalizedOrganization = computed<Organization>(() => ({
    id: 0,
    name: props.organization.name,
    slug: '',
    logo: props.organization.logo,
    logo_url: props.organization.logo_url || (props.organization.logo ? `/storage/${props.organization.logo}` : undefined),
    type: 'business',
    description: undefined,
}));

// Normalize products data
const normalizedProducts = computed<Product[]>(() => {
    const products = props.organization.products || [];
    console.log('ProfilePreview - Normalizing products:', products);
    return products
        .filter((product: any) => product.is_active !== false)
        .map((product: any) => ({
            id: product.id,
            name: product.name,
            slug: product.slug,
            short_description: product.short_description,
            description: product.description,
            price: product.price ? parseFloat(product.price) : undefined,
            sale_price: product.sale_price ? parseFloat(product.sale_price) : undefined,
            currency: product.currency || 'MXN',
            image: product.image ? `/storage/${product.image}` : (product.image_url || undefined),
            gallery: product.gallery,
            external_url: product.external_url,
            whatsapp_message: product.whatsapp_message,
            is_featured: product.is_featured || false,
            is_available: product.is_available ?? true,
            category: product.category ? {
                id: product.category.id,
                name: product.category.name,
                slug: product.category.slug,
            } : undefined,
        }));
});

// Normalize categories data - extract from products if not provided separately
const normalizedCategories = computed<Category[]>(() => {
    // Try to get from organization.productCategories first
    let categories = props.organization.productCategories || [];
    
    // If not available, extract unique categories from products
    if (categories.length === 0) {
        const categoryMap = new Map<number, Category>();
        const products = props.organization.products || [];
        
        products.forEach((product: any) => {
            if (product.category && product.category.id) {
                if (!categoryMap.has(product.category.id)) {
                    categoryMap.set(product.category.id, {
                        id: product.category.id,
                        name: product.category.name,
                        slug: product.category.slug,
                        products_count: 0,
                    });
                }
                // Count products in this category
                const cat = categoryMap.get(product.category.id)!;
                cat.products_count = (cat.products_count || 0) + 1;
            }
        });
        
        categories = Array.from(categoryMap.values());
    }
    
    console.log('ProfilePreview - Normalizing categories:', categories);
    return categories.map((category: any) => ({
        id: category.id,
        name: category.name,
        slug: category.slug,
        products_count: category.products_count || 0,
    }));
});

// Normalize sections data - extract from products if not provided separately
const normalizedSections = computed<ProductSection[]>(() => {
    // Try to get from organization.productSections first
    let sections = props.organization.productSections || [];
    
    // If not available, try to extract from products.sections
    if (sections.length === 0) {
        const sectionMap = new Map<number, any>();
        const products = props.organization.products || [];
        
        products.forEach((product: any) => {
            if (product.sections && Array.isArray(product.sections) && product.sections.length > 0) {
                product.sections.forEach((section: any) => {
                    if (section && section.id) {
                        if (!sectionMap.has(section.id)) {
                            sectionMap.set(section.id, {
                                id: section.id,
                                title: section.title || section.name,
                                slug: section.slug,
                                description: section.description,
                                icon: section.icon,
                                products: [],
                            });
                        }
                        // Add product to section
                        const sec = sectionMap.get(section.id)!;
                        sec.products.push(product);
                    }
                });
            }
        });
        
        sections = Array.from(sectionMap.values());
    }
    
    console.log('ProfilePreview - Normalizing sections:', sections);
    return sections
        .filter((section: any) => section.is_active !== false)
        .map((section: any) => ({
            id: section.id,
            title: section.title,
            slug: section.slug,
            description: section.description,
            icon: section.icon,
            products: (section.products || []).map((product: any) => ({
                id: product.id,
                name: product.name,
                slug: product.slug,
                short_description: product.short_description,
                description: product.description,
                price: product.price ? parseFloat(product.price) : undefined,
                sale_price: product.sale_price ? parseFloat(product.sale_price) : undefined,
                currency: product.currency || 'MXN',
                image: product.image ? `/storage/${product.image}` : (product.image_url || undefined),
                gallery: product.gallery,
                external_url: product.external_url,
                whatsapp_message: product.whatsapp_message,
                is_featured: product.is_featured || false,
                is_available: product.is_available ?? true,
                category: product.category ? {
                    id: product.category.id,
                    name: product.category.name,
                    slug: product.category.slug,
                } : undefined,
            })),
        }));
});

// Preview is always loaded (no animation delay needed)
const isLoaded = computed(() => true);
</script>

<template>
    <div class="relative rounded-2xl border shadow-lg overflow-hidden" style="max-height: 600px;">
        <!-- Phone Frame -->
        <div class="absolute inset-x-0 top-0 h-6 bg-black rounded-t-2xl flex items-center justify-center z-10">
            <div class="w-20 h-4 bg-black rounded-full" />
        </div>

        <!-- Background Layer -->
        <div class="absolute inset-0 z-0 overflow-hidden rounded-2xl">
            <!-- Animated Background (Video/GIF/Image) -->
            <div
                v-if="normalizedSettings.background_type === 'animated' && normalizedSettings.background_animated_media"
                class="absolute inset-0 overflow-hidden rounded-2xl"
            >
                <!-- Video Background -->
                <video
                    v-if="normalizedSettings.background_animated_media_type === 'video'"
                    :src="`/storage/${normalizedSettings.background_animated_media}`"
                    autoplay
                    loop
                    muted
                    playsinline
                    class="absolute inset-0 w-full h-full object-cover"
                />
                <!-- GIF/Image Background -->
                <img
                    v-else
                    :src="`/storage/${normalizedSettings.background_animated_media}`"
                    alt="Background"
                    class="absolute inset-0 w-full h-full object-cover"
                />
                <!-- Overlay for animated backgrounds -->
                <div
                    v-if="(normalizedSettings.background_animated_overlay_opacity || 0) > 0"
                    class="absolute inset-0 bg-black pointer-events-none"
                    :style="{ opacity: (normalizedSettings.background_animated_overlay_opacity || 0) / 100 }"
                />
            </div>
        </div>

        <!-- Content -->
        <div
            class="relative pt-6 overflow-y-auto z-10"
            :style="{ ...backgroundStyle, minHeight: '500px', maxHeight: '600px' }"
        >
            <div class="px-4 py-6">
                <!-- Profile Header -->
                <ProfileHeader
                    :profile="normalizedProfile"
                    :organization="normalizedOrganization"
                    :settings="normalizedSettings"
                    :is-loaded="isLoaded"
                />

                <!-- Social Links -->
                <SocialLinks
                    :links="normalizedProfile.social_links"
                    :settings="normalizedSettings"
                    :is-loaded="isLoaded"
                />

                <!-- Custom Links -->
                <CustomLinks
                    :links="normalizedProfile.custom_links"
                    :settings="normalizedSettings"
                    :is-loaded="isLoaded"
                />

                <!-- Products Catalog -->
                <ProductsCatalog
                    v-if="(normalizedProducts.length > 0) || (normalizedSections.length > 0)"
                    :products="normalizedProducts"
                    :categories="normalizedCategories"
                    :sections="normalizedSections"
                    :settings="normalizedSettings"
                    :is-loaded="isLoaded"
                />

                <!-- Download Contact Button -->
                <div class="flex justify-center mt-6">
                    <button
                        class="inline-flex items-center gap-2 px-6 py-2 rounded-lg text-sm font-medium text-white transition-all hover:scale-105"
                        :style="{ backgroundColor: normalizedSettings.primary_color }"
                    >
                        <Download class="w-4 h-4" />
                        Guardar Contacto
                    </button>
                </div>

                <!-- Views Badge -->
                <div class="flex items-center justify-center gap-1 text-xs opacity-50 mt-4" :style="{ color: normalizedSettings.text_secondary_color }">
                    <Eye class="w-3 h-3" />
                    <span>Vista previa</span>
                </div>
            </div>
        </div>
    </div>
</template>
