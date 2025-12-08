<script setup lang="ts">
import { ref, computed, onBeforeUnmount, nextTick, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import {
    Plus,
    Trash2,
    Package,
    Star,
    Tag,
    Pencil,
} from 'lucide-vue-next';
import { ImageUpload } from '@/components/ui/image-upload';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    organization: any;
    limits: any;
}>();

const swal = useSwal();
const showAddModal = ref(false);
const showCategoryModal = ref(false);
const isUnmounting = ref(false);

const canAddMore = computed(() => (props.organization.products?.length ?? 0) < props.limits.max_products);

// Close modals before unmounting to prevent Vue DOM errors
onBeforeUnmount(async () => {
    isUnmounting.value = true;
    showAddModal.value = false;
    showCategoryModal.value = false;
    await nextTick();
});

const productForm = useForm({
    name: '',
    slug: '',
    category_id: null as number | null,
    short_description: '',
    description: '',
    price: null as number | null,
    sale_price: null as number | null,
    currency: 'MXN',
    image: null as File | null,
    whatsapp_message: '',
    is_featured: false,
    is_available: true,
});

const categoryForm = useForm({
    name: '',
    description: '',
});

function openAddProductModal() {
    productForm.reset();
    productForm.currency = 'MXN';
    productForm.is_available = true;
    showAddModal.value = true;
}

function openCategoryModal() {
    categoryForm.reset();
    showCategoryModal.value = true;
}

function submitProduct() {
    productForm.post(`/admin/organizations/${props.organization.id}/products`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            swal.success('¡Producto creado!');
        },
    });
}

function submitCategory() {
    categoryForm.post(`/admin/organizations/${props.organization.id}/categories`, {
        preserveScroll: true,
        onSuccess: () => {
            showCategoryModal.value = false;
            swal.success('¡Categoría creada!');
        },
    });
}

async function deleteProduct(product: any) {
    const confirmed = await swal.confirmDelete(product.name);
    if (confirmed) {
        router.delete(`/admin/products/${product.id}`, {
            preserveScroll: true,
        });
    }
}

async function deleteCategory(category: any) {
    const confirmed = await swal.confirmDelete(category.name);
    if (confirmed) {
        router.delete(`/admin/categories/${category.id}`, {
            preserveScroll: true,
        });
    }
}

function toggleFeatured(product: any) {
    router.put(`/admin/products/${product.id}`, {
        ...product,
        is_featured: !product.is_featured,
    }, { preserveScroll: true });
}

function toggleAvailable(product: any) {
    router.put(`/admin/products/${product.id}`, {
        ...product,
        is_available: !product.is_available,
    }, { preserveScroll: true });
}

function formatPrice(price: number, currency: string) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: currency,
    }).format(price);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold">Catálogo de Productos</h2>
                <p class="text-sm text-muted-foreground">
                    {{ organization.products?.length ?? 0 }}/{{ limits.max_products }} productos
                </p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="openCategoryModal" class="gap-1">
                    <Tag class="h-4 w-4" />
                    Nueva Categoría
                </Button>
                <Button v-if="canAddMore" @click="openAddProductModal" class="gap-1">
                    <Plus class="h-4 w-4" />
                    Nuevo Producto
                </Button>
            </div>
        </div>

        <!-- Categories -->
        <div v-if="organization.productCategories?.length" class="flex flex-wrap gap-2">
            <div
                v-for="cat in organization.productCategories"
                :key="cat.id"
                class="flex items-center gap-2 rounded-full border bg-muted px-3 py-1 text-sm"
            >
                <span>{{ cat.name }}</span>
                <button @click="deleteCategory(cat)" class="text-muted-foreground hover:text-destructive">
                    <Trash2 class="h-3 w-3" />
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        <div v-if="organization.products?.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
                v-for="product in organization.products"
                :key="product.id"
                class="group rounded-xl border bg-card overflow-hidden transition-all hover:shadow-md"
            >
                <!-- Image -->
                <div class="aspect-square bg-muted relative">
                    <img
                        v-if="product.image"
                        :src="`/storage/${product.image}`"
                        :alt="product.name"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="h-full w-full flex items-center justify-center">
                        <Package class="h-16 w-16 text-muted-foreground/30" />
                    </div>

                    <!-- Badges -->
                    <div class="absolute top-2 left-2 flex gap-1">
                        <span
                            v-if="product.is_featured"
                            class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-medium text-white"
                        >
                            Destacado
                        </span>
                        <span
                            v-if="product.sale_price"
                            class="rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-medium text-white"
                        >
                            Oferta
                        </span>
                    </div>

                    <!-- Quick Actions -->
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <Button size="icon" variant="secondary" class="h-8 w-8" @click="toggleFeatured(product)">
                            <Star
                                class="h-4 w-4"
                                :class="product.is_featured ? 'text-amber-500 fill-amber-500' : ''"
                            />
                        </Button>
                    </div>
                </div>

                <!-- Info -->
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="font-semibold truncate">{{ product.name }}</h3>
                            <p v-if="product.category" class="text-xs text-muted-foreground">
                                {{ product.category.name }}
                            </p>
                        </div>
                    </div>

                    <!-- Price -->
                    <div v-if="product.price" class="mt-2 flex items-center gap-2">
                        <span
                            v-if="product.sale_price"
                            class="font-bold text-primary"
                        >
                            {{ formatPrice(product.sale_price, product.currency) }}
                        </span>
                        <span
                            :class="product.sale_price ? 'text-sm line-through text-muted-foreground' : 'font-bold text-primary'"
                        >
                            {{ formatPrice(product.price, product.currency) }}
                        </span>
                    </div>

                    <!-- Status & Actions -->
                    <div class="mt-3 flex items-center justify-between">
                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="product.is_available
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-gray-100 text-gray-600'"
                        >
                            {{ product.is_available ? 'Disponible' : 'No disponible' }}
                        </span>
                        <Button size="icon" variant="ghost" @click="deleteProduct(product)">
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16 text-muted-foreground">
            <Package class="h-16 w-16 mx-auto mb-4 opacity-50" />
            <h3 class="text-lg font-medium">No hay productos</h3>
            <p class="mt-1">Agrega productos a tu catálogo para mostrarlos en tu perfil.</p>
            <Button @click="openAddProductModal" class="mt-4 gap-2">
                <Plus class="h-4 w-4" />
                Agregar Producto
            </Button>
        </div>

        <!-- Add Product Modal -->
        <Dialog v-model:open="showAddModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-lg max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Nuevo Producto</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitProduct" class="space-y-4">
                    <ImageUpload
                        v-model="productForm.image"
                        label="Imagen"
                        aspect-ratio="square"
                        :max-size="2"
                    />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2 sm:col-span-2">
                            <Label for="product-name">Nombre *</Label>
                            <Input
                                id="product-name"
                                v-model="productForm.name"
                                required
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="product-category">Categoría</Label>
                            <select
                                id="product-category"
                                v-model="productForm.category_id"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-sm"
                            >
                                <option :value="null">Sin categoría</option>
                                <option v-for="cat in organization.productCategories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="product-currency">Moneda</Label>
                            <select
                                id="product-currency"
                                v-model="productForm.currency"
                                class="w-full rounded-lg border bg-background px-3 py-2 text-sm"
                            >
                                <option value="MXN">MXN (Pesos)</option>
                                <option value="USD">USD (Dólares)</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="product-price">Precio</Label>
                            <Input
                                id="product-price"
                                v-model.number="productForm.price"
                                type="number"
                                step="0.01"
                                min="0"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="product-sale_price">Precio de oferta</Label>
                            <Input
                                id="product-sale_price"
                                v-model.number="productForm.sale_price"
                                type="number"
                                step="0.01"
                                min="0"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="product-short_description">Descripción corta</Label>
                        <Input
                            id="product-short_description"
                            v-model="productForm.short_description"
                            maxlength="200"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="product-description">Descripción completa</Label>
                        <textarea
                            id="product-description"
                            v-model="productForm.description"
                            rows="3"
                            class="w-full rounded-lg border bg-background px-4 py-3 text-sm"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="product-whatsapp">Mensaje de WhatsApp</Label>
                        <Input
                            id="product-whatsapp"
                            v-model="productForm.whatsapp_message"
                            placeholder="Hola, me interesa..."
                        />
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="product-featured"
                                :checked="productForm.is_featured"
                                @update:checked="productForm.is_featured = $event"
                            />
                            <Label for="product-featured" class="cursor-pointer">Destacado</Label>
                        </div>
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="product-available"
                                :checked="productForm.is_available"
                                @update:checked="productForm.is_available = $event"
                            />
                            <Label for="product-available" class="cursor-pointer">Disponible</Label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showAddModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="productForm.processing">
                            Crear Producto
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Add Category Modal -->
        <Dialog v-model:open="showCategoryModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Nueva Categoría</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitCategory" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="category-name">Nombre *</Label>
                        <Input
                            id="category-name"
                            v-model="categoryForm.name"
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="category-description">Descripción</Label>
                        <textarea
                            id="category-description"
                            v-model="categoryForm.description"
                            rows="2"
                            class="w-full rounded-lg border bg-background px-4 py-3 text-sm"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showCategoryModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="categoryForm.processing">
                            Crear
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>





