<script setup lang="ts">
import { ref, computed } from 'vue';
import { ShoppingBag } from 'lucide-vue-next';
import ProductCard from './ProductCard.vue';
import type { Product, Category, ProfileSettings } from '@/types/profile';
import { useProfileSettings } from '@/composables/useProfileSettings';

const props = defineProps<{
    products: Product[];
    categories: Category[];
    settings: ProfileSettings;
    isLoaded: boolean;
}>();

const emit = defineEmits<{
    'product-selected': [product: Product];
}>();

const selectedCategory = ref<number | null>(null);
const { glowSettings, cardClasses, cardStyle } = useProfileSettings(props.settings);

const filteredProducts = computed(() => {
    if (!selectedCategory.value) return props.products;
    return props.products.filter((p) => p.category?.id === selectedCategory.value);
});

function handleProductClick(product: Product) {
    emit('product-selected', product);
}
</script>

<template>
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
            <ProductCard
                v-for="product in filteredProducts"
                :key="product.id"
                :product="product"
                :settings="settings"
                :glow-settings="glowSettings"
                :card-classes="cardClasses"
                :card-style="cardStyle"
                @click="handleProductClick"
            />
        </div>
    </div>
</template>

