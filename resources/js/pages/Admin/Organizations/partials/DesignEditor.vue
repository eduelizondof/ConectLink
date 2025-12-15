<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    Palette,
    Image,
    Sparkles,
    Type,
    Wand2,
    Layers,
    RotateCcw,
} from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import ProfilePreview from './ProfilePreview.vue';
import {
    DesignBackgroundSection,
    DesignColorsSection,
    DesignCardsSection,
    DesignAnimationsSection,
    DesignEffectsSection,
    DesignLayoutSection,
} from './design';

interface Profile {
    id: number;
    name: string;
    is_primary: boolean;
    settings?: Record<string, any>;
    [key: string]: any;
}

interface Organization {
    id: number;
    name: string;
    profiles: Profile[];
    [key: string]: any;
}

const props = defineProps<{
    organization: Organization;
    selectedProfileId: number | null;
}>();

const emit = defineEmits<{
    (e: 'selectProfile', id: number): void;
}>();

const selectedProfile = computed(() =>
    props.organization.profiles.find(p => p.id === props.selectedProfileId)
);

// Refs for child components
const backgroundRef = ref<InstanceType<typeof DesignBackgroundSection> | null>(null);
const colorsRef = ref<InstanceType<typeof DesignColorsSection> | null>(null);
const cardsRef = ref<InstanceType<typeof DesignCardsSection> | null>(null);
const animationsRef = ref<InstanceType<typeof DesignAnimationsSection> | null>(null);
const effectsRef = ref<InstanceType<typeof DesignEffectsSection> | null>(null);
const layoutRef = ref<InstanceType<typeof DesignLayoutSection> | null>(null);

// Accordion state
const openSections = ref(['background', 'colors']);

// Computed preview settings - combines all section data for live preview
const previewSettings = computed(() => {
    const base = selectedProfile.value?.settings || {};
    
    return {
        ...base,
        ...(backgroundRef.value?.formData || {}),
        ...(colorsRef.value?.formData || {}),
        ...(cardsRef.value?.formData || {}),
        ...(animationsRef.value?.formData || {}),
        ...(effectsRef.value?.formData || {}),
        ...(layoutRef.value?.formData || {}),
    };
});

const previewProfile = computed(() => {
    if (!selectedProfile.value) return null;
    return {
        ...selectedProfile.value,
        settings: previewSettings.value,
    };
});

function handleSectionUpdated() {
    // Refresh page data to get updated settings
    router.reload({ only: ['organization'] });
}

function resetToDefaults() {
    // Reload page to reset form states
    router.reload({ only: ['organization'] });
}
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Design Options -->
        <div class="space-y-6">
            <!-- Profile Selector & Actions -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <Label>Perfil:</Label>
                    <select
                        :value="selectedProfileId"
                        @change="emit('selectProfile', Number(($event.target as HTMLSelectElement).value))"
                        class="rounded-lg border bg-background px-3 py-2 text-sm"
                    >
                        <option v-for="profile in organization.profiles" :key="profile.id" :value="profile.id">
                            {{ profile.name }} {{ profile.is_primary ? '(Principal)' : '' }}
                        </option>
                    </select>
                </div>
                <Button variant="outline" size="sm" @click="resetToDefaults" class="gap-1">
                    <RotateCcw class="h-4 w-4" />
                    Restablecer
                </Button>
            </div>

            <!-- Settings Accordion -->
            <div class="rounded-xl border bg-card">
                <Accordion type="multiple" v-model="openSections" class="w-full">
                    <!-- Background Section -->
                    <AccordionItem value="background">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Image class="h-4 w-4 text-muted-foreground" />
                                <span>Fondo</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <DesignBackgroundSection
                                ref="backgroundRef"
                                :profile="selectedProfile"
                                @updated="handleSectionUpdated"
                            />
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Colors Section -->
                    <AccordionItem value="colors">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Palette class="h-4 w-4 text-muted-foreground" />
                                <span>Colores</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <DesignColorsSection
                                ref="colorsRef"
                                :profile="selectedProfile"
                                @updated="handleSectionUpdated"
                            />
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Cards Section -->
                    <AccordionItem value="cards">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Layers class="h-4 w-4 text-muted-foreground" />
                                <span>Tarjetas</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <DesignCardsSection
                                ref="cardsRef"
                                :profile="selectedProfile"
                                @updated="handleSectionUpdated"
                            />
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Animations Section -->
                    <AccordionItem value="animations">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Wand2 class="h-4 w-4 text-muted-foreground" />
                                <span>Animaciones</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <DesignAnimationsSection
                                ref="animationsRef"
                                :profile="selectedProfile"
                                @updated="handleSectionUpdated"
                            />
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Visual Effects Section -->
                    <AccordionItem value="effects">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Sparkles class="h-4 w-4 text-muted-foreground" />
                                <span>Efectos visuales</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <DesignEffectsSection
                                ref="effectsRef"
                                :profile="selectedProfile"
                                @updated="handleSectionUpdated"
                            />
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Layout Section -->
                    <AccordionItem value="layout">
                        <AccordionTrigger class="px-4">
                            <div class="flex items-center gap-2">
                                <Type class="h-4 w-4 text-muted-foreground" />
                                <span>Diseño y tipografía</span>
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="px-4 pb-4">
                            <DesignLayoutSection
                                ref="layoutRef"
                                :profile="selectedProfile"
                                @updated="handleSectionUpdated"
                            />
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>
        </div>

        <!-- Preview -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Vista previa</h2>
                <span class="rounded-full bg-muted px-2 py-1 text-xs text-muted-foreground">
                    Tiempo real
                </span>
            </div>
            <ProfilePreview
                v-if="previewProfile"
                :profile="previewProfile"
                :organization="organization"
            />
            <p v-else class="text-center text-muted-foreground py-12">
                Selecciona un perfil para ver la vista previa
            </p>
        </div>
    </div>
</template>
