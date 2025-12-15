<script setup lang="ts">
import { computed } from 'vue';
import { Star, Package } from 'lucide-vue-next';
import { GlowingBorder } from '@/components/effects';
import type { Product, ProfileSettings } from '@/types/profile';
import { formatPrice } from '@/composables/useProfileHelpers';

const props = defineProps<{
    product: Product;
    settings: ProfileSettings;
    glowSettings: {
        enabled: boolean;
        color: string;
        colorSecondary: string;
        variant: string;
        borderRadius: string;
    };
    cardClasses: string;
    cardStyle: Record<string, string>;
}>();

const emit = defineEmits<{
    click: [product: Product];
}>();

// Compute the image URL, handling both old format (relative path) and new format (full URL)
const imageUrl = computed(() => {
    if (!props.product.image) return null;
    // If already a full URL or starts with /storage/, use as-is
    if (props.product.image.startsWith('http') || props.product.image.startsWith('/storage/')) {
        return props.product.image;
    }
    // Otherwise, prepend /storage/
    return `/storage/${props.product.image}`;
});

function handleClick() {
    emit('click', props.product);
}
</script>

<template>
    <component
        :is="glowSettings.enabled ? GlowingBorder : 'div'"
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
            @click="handleClick"
        >
            <!-- Product Image -->
            <div class="aspect-square rounded-t-lg overflow-hidden bg-gray-100 relative">
                <img
                    v-if="imageUrl"
                    :src="imageUrl"
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
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

