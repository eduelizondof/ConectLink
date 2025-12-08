<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useSwal } from '@/composables/useSwal';
import { ref, watch, onMounted } from 'vue';
import { Building2, User, ArrowRight, Sparkles } from 'lucide-vue-next';
import { RadioCard } from '@/components/ui/radio-card';
import { ImageUpload } from '@/components/ui/image-upload';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

const page = usePage();
const swal = useSwal();

const form = useForm({
    name: '',
    slug: '',
    type: 'business' as 'business' | 'personal',
    description: '',
    logo: null as File | null,
});

const slugTouched = ref(false);
const isGeneratingSlug = ref(false);

// Auto-generate slug from name
watch(() => form.name, async (name) => {
    if (!slugTouched.value && name) {
        isGeneratingSlug.value = true;
        try {
            const response = await fetch('/admin/organizations/suggest-slug', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ name }),
            });
            const data = await response.json();
            form.slug = data.slug;
        } catch (e) {
            // Fallback to simple slug
            form.slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        }
        isGeneratingSlug.value = false;
    }
});

function onSlugInput() {
    slugTouched.value = true;
}

function submit() {
    form.post('/admin/organizations', {
        forceFormData: true,
    });
}

onMounted(() => {
    const flash = page.props.flash as { error?: string } | undefined;
    if (flash?.error) swal.error(flash.error);
});
</script>

<template>
    <Head title="Nueva Organización" />

    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/admin' },
        { title: 'Organizaciones', href: '/admin/organizations' },
        { title: 'Crear', href: '/admin/organizations/create' },
    ]">
        <div class="flex-1 p-4 md:p-6">
            <div class="mx-auto max-w-2xl">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600">
                        <Sparkles class="h-8 w-8 text-white" />
                    </div>
                    <h1 class="mt-4 text-2xl font-bold">Crear Organización</h1>
                    <p class="mt-2 text-muted-foreground">
                        Configura tu empresa, negocio o perfil personal
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Type Selection -->
                    <div class="space-y-4">
                        <Label class="text-base font-semibold">Tipo de organización</Label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <RadioCard
                                v-model="form.type"
                                value="business"
                                label="Empresa / Negocio"
                                description="Para negocios con múltiples empleados o departamentos"
                                :icon="Building2"
                            />
                            <RadioCard
                                v-model="form.type"
                                value="personal"
                                label="Personal"
                                description="Para freelancers, profesionales o marca personal"
                                :icon="User"
                            />
                        </div>
                    </div>

                    <!-- Logo -->
                    <div class="space-y-4">
                        <Label class="text-base font-semibold">Logo (opcional)</Label>
                        <ImageUpload
                            v-model="form.logo"
                            aspect-ratio="square"
                            placeholder="Sube el logo de tu organización"
                            :max-size="2"
                        />
                    </div>

                    <!-- Name -->
                    <div class="space-y-2">
                        <Label for="name" class="text-base font-semibold">Nombre</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Mi Empresa"
                            class="h-12"
                            required
                        />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <!-- Slug -->
                    <div class="space-y-2">
                        <Label for="slug" class="text-base font-semibold">URL personalizada</Label>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-muted-foreground whitespace-nowrap">
                                conectlink.cnva.mx/
                            </span>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                placeholder="mi-empresa"
                                class="h-12 font-mono"
                                maxlength="60"
                                required
                                @input="onSlugInput"
                            />
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Solo letras minúsculas, números y guiones. Máximo 60 caracteres.
                        </p>
                        <p v-if="form.errors.slug" class="text-sm text-destructive">{{ form.errors.slug }}</p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <Label for="description" class="text-base font-semibold">Descripción (opcional)</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border bg-background px-4 py-3 text-sm"
                            placeholder="Breve descripción de tu organización..."
                            maxlength="500"
                        />
                        <p class="text-xs text-muted-foreground text-right">
                            {{ form.description.length }}/500
                        </p>
                        <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-4 pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit('/admin/organizations')"
                        >
                            Cancelar
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="gap-2"
                        >
                            Crear Organización
                            <ArrowRight class="h-4 w-4" />
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>





