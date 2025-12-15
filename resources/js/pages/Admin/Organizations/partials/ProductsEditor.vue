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
    LayoutGrid,
    Settings2,
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
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';

const props = defineProps<{
    organization: any;
    limits: any;
}>();

const swal = useSwal();
const showAddModal = ref(false);
const showCategoryModal = ref(false);
const showEditCategoryModal = ref(false);
const showSectionModal = ref(false);
const showEditSectionModal = ref(false);
const showAssignProductsModal = ref(false);
const isUnmounting = ref(false);
const editingProduct = ref<any>(null);
const editingCategory = ref<any>(null);
const editingSection = ref<any>(null);
const activeTab = ref('products');

// Ensure productSections is always an array
// Inertia may serialize relationships in snake_case (product_sections) or camelCase (productSections)
const productSections = computed(() => {
    const sections = props.organization?.productSections || props.organization?.product_sections;
    console.log('productSections computed:', {
        organization: props.organization,
        productSections: props.organization?.productSections,
        product_sections: props.organization?.product_sections,
        sections: sections,
        isArray: Array.isArray(sections),
        type: typeof sections,
    });
    if (!sections) return [];
    return Array.isArray(sections) ? sections : [];
});

const canAddMore = computed(() => (props.organization.products?.length ?? 0) < props.limits.max_products);
const canAddMoreSections = computed(() => productSections.value.length < (props.limits.max_sections ?? 10));

// Close modals before unmounting to prevent Vue DOM errors
onBeforeUnmount(async () => {
    isUnmounting.value = true;
    showAddModal.value = false;
    showCategoryModal.value = false;
    showEditCategoryModal.value = false;
    showSectionModal.value = false;
    showEditSectionModal.value = false;
    showAssignProductsModal.value = false;
    await nextTick();
});

const productForm = useForm({
    name: '',
    slug: '',
    category_id: null as number | null,
    short_description: '',
    description: '',
    price: undefined as number | undefined,
    sale_price: undefined as number | undefined,
    currency: 'MXN',
    image: null as File | null,
    whatsapp_message: '',
    is_featured: false,
    is_available: true,
    section_ids: [] as number[],
});

const categoryForm = useForm({
    name: '',
    description: '',
});

const editCategoryForm = useForm({
    name: '',
    slug: '',
    description: '',
    is_active: true,
});

const sectionForm = useForm({
    title: '',
    description: '',
});

const editSectionForm = useForm({
    title: '',
    slug: '',
    description: '',
    is_active: true,
});

const assignProductsForm = useForm({
    product_ids: [] as number[],
});

function openAddProductModal() {
    editingProduct.value = null;
    productForm.reset();
    productForm.currency = 'MXN';
    productForm.is_available = true;
    productForm.section_ids = [];
    showAddModal.value = true;
}

function openEditProductModal(product: any) {
    editingProduct.value = product;
    productForm.name = product.name;
    productForm.slug = product.slug || '';
    productForm.category_id = product.category_id;
    productForm.short_description = product.short_description || '';
    productForm.description = product.description || '';
    productForm.price = product.price;
    productForm.sale_price = product.sale_price;
    productForm.currency = product.currency || 'MXN';
    productForm.image = null;
    productForm.whatsapp_message = product.whatsapp_message || '';
    productForm.is_featured = product.is_featured;
    productForm.is_available = product.is_available;
    productForm.section_ids = product.sections?.map((s: any) => s.id) || [];
    showAddModal.value = true;
}

function openCategoryModal() {
    editingCategory.value = null;
    categoryForm.reset();
    showCategoryModal.value = true;
}

function openEditCategoryModal(category: any) {
    editingCategory.value = category;
    editCategoryForm.name = category.name;
    editCategoryForm.slug = category.slug || '';
    editCategoryForm.description = category.description || '';
    editCategoryForm.is_active = category.is_active ?? true;
    showEditCategoryModal.value = true;
}

function openSectionModal() {
    editingSection.value = null;
    sectionForm.reset();
    showSectionModal.value = true;
}

function openEditSectionModal(section: any) {
    editingSection.value = section;
    editSectionForm.title = section.title;
    editSectionForm.slug = section.slug || '';
    editSectionForm.description = section.description || '';
    editSectionForm.is_active = section.is_active ?? true;
    showEditSectionModal.value = true;
}

function openAssignProductsModal(section: any) {
    editingSection.value = section;
    assignProductsForm.product_ids = section.products?.map((p: any) => p.id) || [];
    showAssignProductsModal.value = true;
}

function submitProduct() {
    if (editingProduct.value) {
        // Use _method spoofing for PUT with file uploads
        productForm
            .transform((data) => ({
                ...data,
                _method: 'PUT',
            }))
            .post(`/admin/products/${editingProduct.value.id}`, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    // Update sections if changed
                    if (editingProduct.value) {
                        router.put(`/admin/products/${editingProduct.value.id}/sections`, {
                            section_ids: productForm.section_ids,
                        }, {
                            preserveScroll: true,
                            onSuccess: () => {
                                showAddModal.value = false;
                                editingProduct.value = null;
                                swal.success('¡Producto actualizado!');
                            },
                        });
                    }
                },
            });
    } else {
        productForm.post(`/admin/organizations/${props.organization.id}/products`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                showAddModal.value = false;
                swal.success('¡Producto creado!');
            },
        });
    }
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

function submitEditCategory() {
    editCategoryForm.put(`/admin/categories/${editingCategory.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditCategoryModal.value = false;
            editingCategory.value = null;
            swal.success('¡Categoría actualizada!');
        },
    });
}

function submitSection() {
    sectionForm.post(`/admin/organizations/${props.organization.id}/sections`, {
        preserveScroll: true,
        onSuccess: () => {
            showSectionModal.value = false;
            sectionForm.reset();
            swal.success('¡Sección creada!');
            // Force reload to get updated sections
            router.reload({ only: ['organization'] });
        },
    });
}

function submitEditSection() {
    editSectionForm.put(`/admin/sections/${editingSection.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditSectionModal.value = false;
            editingSection.value = null;
            swal.success('¡Sección actualizada!');
            // Force reload to get updated sections
            router.reload({ only: ['organization'] });
        },
    });
}

function submitAssignProducts() {
    assignProductsForm.post(`/admin/sections/${editingSection.value.id}/products`, {
        preserveScroll: true,
        onSuccess: () => {
            showAssignProductsModal.value = false;
            editingSection.value = null;
            swal.success('¡Productos asignados!');
            // Force reload to get updated sections
            router.reload({ only: ['organization'] });
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

async function deleteSection(section: any) {
    const confirmed = await swal.confirmDelete(section.title);
    if (confirmed) {
        router.delete(`/admin/sections/${section.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Force reload to get updated sections
                router.reload({ only: ['organization'] });
            },
        });
    }
}

function toggleFeatured(product: any) {
    router.put(`/admin/products/${product.id}`, {
        ...product,
        is_featured: !product.is_featured,
    }, { preserveScroll: true });
}

function formatPrice(price: number, currency: string) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: currency,
    }).format(price);
}

function getProductImageUrl(product: any) {
    if (!product.image) return null;
    if (product.image.startsWith('http') || product.image.startsWith('/storage/')) {
        return product.image;
    }
    return `/storage/${product.image}`;
}

function toggleProductInSection(productId: number) {
    const index = assignProductsForm.product_ids.indexOf(productId);
    if (index > -1) {
        assignProductsForm.product_ids.splice(index, 1);
    } else {
        assignProductsForm.product_ids.push(productId);
    }
}

function toggleSectionForProduct(sectionId: number) {
    const index = productForm.section_ids.indexOf(sectionId);
    if (index > -1) {
        productForm.section_ids.splice(index, 1);
    } else {
        productForm.section_ids.push(sectionId);
    }
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
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" @click="openCategoryModal" class="gap-1">
                    <Tag class="h-4 w-4" />
                    Nueva Categoría
                </Button>
                <Button v-if="canAddMoreSections" variant="outline" @click="openSectionModal" class="gap-1">
                    <LayoutGrid class="h-4 w-4" />
                    Nueva Sección
                </Button>
                <Button v-if="canAddMore" @click="openAddProductModal" class="gap-1">
                    <Plus class="h-4 w-4" />
                    Nuevo Producto
                </Button>
            </div>
        </div>

        <!-- Tabs for Products and Sections -->
        <Tabs v-model="activeTab" class="space-y-4">
            <TabsList>
                <TabsTrigger value="products" class="gap-2">
                    <Package class="h-4 w-4" />
                    Productos
                </TabsTrigger>
                <TabsTrigger value="sections" class="gap-2">
                    <LayoutGrid class="h-4 w-4" />
                    Secciones
                    <span v-if="productSections.length > 0" class="ml-1 rounded-full bg-muted px-1.5 py-0.5 text-xs">
                        {{ productSections.length }}
                    </span>
                </TabsTrigger>
            </TabsList>

            <!-- Products Tab -->
            <TabsContent value="products">
                <!-- Categories -->
                <div v-if="organization.productCategories?.length" class="flex flex-wrap gap-2 mb-4">
                    <div
                        v-for="cat in organization.productCategories"
                        :key="cat.id"
                        class="flex items-center gap-2 rounded-full border bg-muted px-3 py-1 text-sm"
                    >
                        <span>{{ cat.name }}</span>
                        <button @click="openEditCategoryModal(cat)" class="text-muted-foreground hover:text-primary">
                            <Pencil class="h-3 w-3" />
                        </button>
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
                        <div class="aspect-square bg-muted relative overflow-hidden">
                            <img
                                v-if="product.image"
                                :src="getProductImageUrl(product)"
                                :alt="product.name"
                                class="h-full w-full object-cover"
                                @error="(e: any) => { e.target.style.display = 'none'; }"
                            />
                            <div 
                                v-if="!product.image"
                                class="h-full w-full flex items-center justify-center"
                            >
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

                            <!-- Sections badges -->
                            <div v-if="product.sections?.length" class="mt-2 flex flex-wrap gap-1">
                                <span
                                    v-for="section in product.sections"
                                    :key="section.id"
                                    class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-medium text-primary"
                                >
                                    {{ section.title }}
                                </span>
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
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                        : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                                >
                                    {{ product.is_available ? 'Disponible' : 'No disponible' }}
                                </span>
                                <div class="flex items-center gap-1">
                                    <Button size="icon" variant="ghost" @click="openEditProductModal(product)">
                                        <Pencil class="h-4 w-4 text-muted-foreground" />
                                    </Button>
                                    <Button size="icon" variant="ghost" @click="deleteProduct(product)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
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
            </TabsContent>

            <!-- Sections Tab -->
            <TabsContent value="sections">
                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Las secciones te permiten agrupar productos con un título personalizable.
                        Por ejemplo: "Lo más preguntado", "Mis productos", "Ofertas especiales".
                    </p>

                    <!-- Debug info (temporary) -->
                    <div class="text-xs text-muted-foreground p-2 bg-muted rounded">
                        <p>Debug: productSections.length = {{ productSections.length }}</p>
                        <p>Debug: organization.productSections = {{ JSON.stringify(organization?.productSections) }}</p>
                    </div>

                    <!-- Sections List -->
                    <div v-if="productSections.length > 0" class="space-y-4">
                        <div
                            v-for="section in productSections"
                            :key="section.id"
                            class="rounded-xl border bg-card p-4"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="font-semibold">{{ section.title }}</h3>
                                    <p v-if="section.description" class="text-sm text-muted-foreground">
                                        {{ section.description }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Button size="sm" variant="outline" @click="openAssignProductsModal(section)" class="gap-1">
                                        <Settings2 class="h-4 w-4" />
                                        Productos
                                    </Button>
                                    <Button size="icon" variant="ghost" @click="openEditSectionModal(section)">
                                        <Pencil class="h-4 w-4 text-muted-foreground" />
                                    </Button>
                                    <Button size="icon" variant="ghost" @click="deleteSection(section)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Section Products -->
                            <div v-if="section.products?.length" class="flex flex-wrap gap-2">
                                <div
                                    v-for="product in section.products"
                                    :key="product.id"
                                    class="flex items-center gap-2 rounded-lg border bg-muted/50 px-3 py-2"
                                >
                                    <div class="h-8 w-8 rounded overflow-hidden bg-muted">
                                        <img
                                            v-if="product.image"
                                            :src="getProductImageUrl(product)"
                                            :alt="product.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <Package v-else class="h-full w-full p-1 text-muted-foreground/30" />
                                    </div>
                                    <span class="text-sm font-medium">{{ product.name }}</span>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground italic">
                                No hay productos en esta sección
                            </p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16 text-muted-foreground">
                        <LayoutGrid class="h-16 w-16 mx-auto mb-4 opacity-50" />
                        <h3 class="text-lg font-medium">No hay secciones</h3>
                        <p class="mt-1">Crea secciones para organizar tus productos con títulos personalizados.</p>
                        <Button @click="openSectionModal" class="mt-4 gap-2">
                            <Plus class="h-4 w-4" />
                            Crear Sección
                        </Button>
                    </div>
                </div>
            </TabsContent>
        </Tabs>

        <!-- Add/Edit Product Modal -->
        <Dialog v-model:open="showAddModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-lg max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{ editingProduct ? 'Editar Producto' : 'Nuevo Producto' }}</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitProduct" class="space-y-4">
                    <ImageUpload
                        v-model="productForm.image"
                        :current-image="editingProduct?.image"
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

                    <!-- Sections -->
                    <div v-if="productSections.length > 0 && editingProduct" class="space-y-2">
                        <Label>Secciones</Label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="section in productSections"
                                :key="section.id"
                                type="button"
                                class="rounded-full px-3 py-1 text-sm transition-colors"
                                :class="productForm.section_ids.includes(section.id)
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted hover:bg-muted/80'"
                                @click="toggleSectionForProduct(section.id)"
                            >
                                {{ section.title }}
                            </button>
                        </div>
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
                            {{ editingProduct ? 'Guardar Cambios' : 'Crear Producto' }}
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

        <!-- Edit Category Modal -->
        <Dialog v-model:open="showEditCategoryModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Editar Categoría</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitEditCategory" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="edit-category-name">Nombre *</Label>
                        <Input
                            id="edit-category-name"
                            v-model="editCategoryForm.name"
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-category-slug">Slug</Label>
                        <Input
                            id="edit-category-slug"
                            v-model="editCategoryForm.slug"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-category-description">Descripción</Label>
                        <textarea
                            id="edit-category-description"
                            v-model="editCategoryForm.description"
                            rows="2"
                            class="w-full rounded-lg border bg-background px-4 py-3 text-sm"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="edit-category-active"
                            :checked="editCategoryForm.is_active"
                            @update:checked="editCategoryForm.is_active = $event"
                        />
                        <Label for="edit-category-active" class="cursor-pointer">Activa</Label>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showEditCategoryModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="editCategoryForm.processing">
                            Guardar
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Add Section Modal -->
        <Dialog v-model:open="showSectionModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Nueva Sección</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitSection" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="section-title">Título *</Label>
                        <Input
                            id="section-title"
                            v-model="sectionForm.title"
                            placeholder="Ej: Lo más preguntado, Mis productos..."
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="section-description">Descripción</Label>
                        <textarea
                            id="section-description"
                            v-model="sectionForm.description"
                            rows="2"
                            class="w-full rounded-lg border bg-background px-4 py-3 text-sm"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showSectionModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="sectionForm.processing">
                            Crear
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Section Modal -->
        <Dialog v-model:open="showEditSectionModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Editar Sección</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitEditSection" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="edit-section-title">Título *</Label>
                        <Input
                            id="edit-section-title"
                            v-model="editSectionForm.title"
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-section-slug">Slug</Label>
                        <Input
                            id="edit-section-slug"
                            v-model="editSectionForm.slug"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-section-description">Descripción</Label>
                        <textarea
                            id="edit-section-description"
                            v-model="editSectionForm.description"
                            rows="2"
                            class="w-full rounded-lg border bg-background px-4 py-3 text-sm"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="edit-section-active"
                            :checked="editSectionForm.is_active"
                            @update:checked="editSectionForm.is_active = $event"
                        />
                        <Label for="edit-section-active" class="cursor-pointer">Activa</Label>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showEditSectionModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="editSectionForm.processing">
                            Guardar
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Assign Products Modal -->
        <Dialog v-model:open="showAssignProductsModal" :modal="true">
            <DialogContent v-if="!isUnmounting" class="max-w-md max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Productos en "{{ editingSection?.title }}"</DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitAssignProducts" class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Selecciona los productos que quieres mostrar en esta sección.
                    </p>

                    <div class="space-y-2 max-h-[400px] overflow-y-auto">
                        <button
                            v-for="product in organization.products"
                            :key="product.id"
                            type="button"
                            class="w-full flex items-center gap-3 p-3 rounded-lg border transition-colors"
                            :class="assignProductsForm.product_ids.includes(product.id)
                                ? 'border-primary bg-primary/5'
                                : 'hover:bg-muted/50'"
                            @click="toggleProductInSection(product.id)"
                        >
                            <div class="h-12 w-12 rounded-lg overflow-hidden bg-muted flex-shrink-0">
                                <img
                                    v-if="product.image"
                                    :src="getProductImageUrl(product)"
                                    :alt="product.name"
                                    class="h-full w-full object-cover"
                                />
                                <Package v-else class="h-full w-full p-2 text-muted-foreground/30" />
                            </div>
                            <div class="flex-1 text-left min-w-0">
                                <p class="font-medium truncate">{{ product.name }}</p>
                                <p v-if="product.price" class="text-sm text-muted-foreground">
                                    {{ formatPrice(product.price, product.currency) }}
                                </p>
                            </div>
                            <div
                                class="h-5 w-5 rounded-full border-2 flex-shrink-0"
                                :class="assignProductsForm.product_ids.includes(product.id)
                                    ? 'border-primary bg-primary'
                                    : 'border-muted-foreground/30'"
                            >
                                <svg
                                    v-if="assignProductsForm.product_ids.includes(product.id)"
                                    class="h-full w-full text-white p-0.5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </button>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showAssignProductsModal = false">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="assignProductsForm.processing">
                            Guardar
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
