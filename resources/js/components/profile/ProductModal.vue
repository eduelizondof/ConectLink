<script setup lang="ts">
import { computed } from 'vue';
import { X, Package } from 'lucide-vue-next';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faWhatsapp } from '@fortawesome/free-brands-svg-icons';
import type { Product, Profile, ProfileSettings } from '@/types/profile';
import { formatPrice } from '@/composables/useProfileHelpers';

const props = defineProps<{
    modelValue: boolean;
    product: Product | null;
    profile: Profile;
    settings: ProfileSettings;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

function closeModal() {
    emit('update:modelValue', false);
}

// Compute the image URL, handling both old format (relative path) and new format (full URL)
const imageUrl = computed(() => {
    if (!props.product?.image) return null;
    // If already a full URL or starts with /storage/, use as-is
    if (props.product.image.startsWith('http') || props.product.image.startsWith('/storage/')) {
        return props.product.image;
    }
    // Otherwise, prepend /storage/
    return `/storage/${props.product.image}`;
});

const whatsappLink = computed(() => {
    const whatsapp = props.profile.social_links.find((l) => l.platform === 'whatsapp');
    if (!whatsapp || !props.product) return null;
    return `${whatsapp.url}?text=${encodeURIComponent(`Hola, me interesa: ${props.product.name}`)}`;
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="modelValue && product"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
            @click.self="closeModal"
        >
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal" />

            <!-- Modal Content -->
            <div
                class="relative bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto animate-slide-up"
            >
                <!-- Close Button -->
                <button
                    @click="closeModal"
                    class="absolute top-4 right-4 z-10 p-2 rounded-full bg-black/10 hover:bg-black/20 transition-colors"
                >
                    <X class="w-5 h-5" />
                </button>

                <!-- Product Image -->
                <div class="aspect-video w-full overflow-hidden rounded-t-3xl sm:rounded-t-3xl bg-gray-100">
                    <img
                        v-if="imageUrl"
                        :src="imageUrl"
                        :alt="product.name"
                        class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center">
                        <Package class="w-20 h-20 text-gray-300" />
                    </div>
                </div>

                <!-- Product Details -->
                <div class="p-6">
                    <!-- Category Badge -->
                    <div v-if="product.category" class="mb-2">
                        <span
                            class="inline-block px-3 py-1 text-xs font-medium rounded-full"
                            :style="{
                                backgroundColor: `${settings.primary_color}20`,
                                color: settings.primary_color,
                            }"
                        >
                            {{ product.category.name }}
                        </span>
                    </div>

                    <!-- Name -->
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ product.name }}</h2>

                    <!-- Price -->
                    <div v-if="product.price" class="flex items-center gap-3 mb-4">
                        <span
                            v-if="product.sale_price && product.sale_price < product.price"
                            class="text-2xl font-bold"
                            :style="{ color: settings.primary_color }"
                        >
                            {{ formatPrice(product.sale_price, product.currency) }}
                        </span>
                        <span
                            :class="product.sale_price && product.sale_price < product.price ? 'line-through text-lg text-gray-400' : 'text-2xl font-bold'"
                            :style="{
                                color: product.sale_price && product.sale_price < product.price ? '' : settings.primary_color,
                            }"
                        >
                            {{ formatPrice(product.price, product.currency) }}
                        </span>
                        <span
                            v-if="product.sale_price && product.sale_price < product.price"
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-red-500 text-white"
                        >
                            -{{ Math.round(((product.price - product.sale_price) / product.price) * 100) }}%
                        </span>
                    </div>

                    <!-- Description -->
                    <p v-if="product.short_description" class="text-gray-600 mb-4">
                        {{ product.short_description }}
                    </p>
                    <div v-if="product.description" class="prose prose-sm text-gray-600 mb-6">
                        {{ product.description }}
                    </div>

                    <!-- Availability -->
                    <div class="flex items-center gap-2 mb-6">
                        <div
                            class="w-2 h-2 rounded-full"
                            :class="product.is_available ? 'bg-green-500' : 'bg-red-500'"
                        />
                        <span class="text-sm" :class="product.is_available ? 'text-green-600' : 'text-red-600'">
                            {{ product.is_available ? 'Disponible' : 'No disponible' }}
                        </span>
                    </div>

                    <!-- CTA Button -->
                    <a
                        v-if="whatsappLink"
                        :href="whatsappLink"
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
</template>

<style scoped>
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
</style>

