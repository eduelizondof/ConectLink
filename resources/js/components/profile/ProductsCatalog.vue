<script setup lang="ts">
import { ref, computed } from 'vue';
import { ShoppingBag } from 'lucide-vue-next';
import ProductCard from './ProductCard.vue';
import type { Product, Category, ProfileSettings, ProductSection } from '@/types/profile';
import { useProfileSettings } from '@/composables/useProfileSettings';

const props = defineProps<{
    products: Product[];
    categories: Category[];
    sections?: ProductSection[];
    settings: ProfileSettings;
    isLoaded: boolean;
}>();

const emit = defineEmits<{
    'product-selected': [product: Product];
}>();

const selectedCategory = ref<number | null>(null);
const { glowSettings, cardClasses, cardStyle } = useProfileSettings(props.settings);

// Normalize sections to always be an array
const normalizedSections = computed(() => {
    return props.sections && Array.isArray(props.sections) ? props.sections : [];
});

// Check if we have sections with products
const hasSections = computed(() => {
    return normalizedSections.value.length > 0 && 
           normalizedSections.value.some(s => s && s.products && Array.isArray(s.products) && s.products.length > 0);
});

// Products not in any section (for the general catalog)
const productsNotInSections = computed(() => {
    if (!hasSections.value) return props.products || [];
    
    const sectionProductIds = new Set<number>();
    normalizedSections.value.forEach(section => {
        if (section && section.products && Array.isArray(section.products)) {
            section.products.forEach(p => {
                if (p && p.id) sectionProductIds.add(p.id);
            });
        }
    });
    
    return (props.products || []).filter(p => p && !sectionProductIds.has(p.id));
});

const filteredProducts = computed(() => {
    const products = productsNotInSections.value;
    if (!selectedCategory.value) return products;
    return products.filter((p) => p.category?.id === selectedCategory.value);
});

function handleProductClick(product: Product) {
    emit('product-selected', product);
}

// Check if there's any content to show
const hasContent = computed(() => {
    return hasSections.value || (props.products && props.products.length > 0);
});
</script>

<template>
    <div v-if="hasContent">
        <!-- Sections with custom titles -->
        <template v-if="hasSections">
            <template
                v-for="(section, sectionIndex) in normalizedSections"
                :key="section.id"
            >
                <div
                    v-if="section && section.products && Array.isArray(section.products) && section.products.length > 0"
                    class="mb-8"
                    :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
                    :style="{ transition: `all 0.6s ease-out ${0.2 + sectionIndex * 0.1}s` }"
                >
            <!-- Section Header -->
            <div class="flex items-center gap-2 mb-4">
                <ShoppingBag class="w-5 h-5" :style="{ color: settings.primary_color }" />
                <h2 class="text-lg font-bold" :style="{ color: settings.text_color }">{{ section.title }}</h2>
            </div>

            <!-- Section Description -->
            <p
                v-if="section.description"
                class="text-sm mb-4"
                :style="{ color: settings.text_secondary_color }"
            >
                {{ section.description }}
            </p>

            <!-- Section Products Grid -->
            <div class="grid grid-cols-2 gap-3">
                <ProductCard
                    v-for="product in section.products"
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
        </template>

        <!-- General Catalog (products not in sections or when no sections exist) -->
        <div
            v-if="filteredProducts.length > 0 || !hasSections"
            id="catalogo"
            class="mb-8"
            :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
            :style="{ transition: `all 0.6s ease-out ${hasSections ? 0.2 + normalizedSections.length * 0.1 : 0.3}s` }"
        >
            <!-- Only show header if there are products to display -->
            <template v-if="filteredProducts.length > 0">
                <!-- Section Header -->
                <div class="flex items-center gap-2 mb-4">
                    <ShoppingBag class="w-5 h-5" :style="{ color: settings.primary_color }" />
                    <h2 class="text-lg font-bold" :style="{ color: settings.text_color }">
                        {{ hasSections ? 'Más Productos' : 'Catálogo' }}
                    </h2>
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
            </template>
        </div>
    </div>
</template>
