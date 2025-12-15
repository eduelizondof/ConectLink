<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next';
import { GlowingBorder } from '@/components/effects';
import type { CustomLink, ProfileSettings } from '@/types/profile';
import { useProfileHelpers } from '@/composables/useProfileHelpers';

const props = defineProps<{
    link: CustomLink;
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
    animationDelay: number;
}>();

const emit = defineEmits<{
    click: [linkId: number];
}>();

const { getImageShapeClass, getCardLayoutClass, getEntranceAnimationClass } = useProfileHelpers();

function handleClick() {
    if (props.link.url) {
        emit('click', props.link.id);
    }
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
        :style="{ animationDelay: `${animationDelay}ms` }"
        :class="getEntranceAnimationClass(settings.animation_entrance)"
    >
        <component
            :is="link.url ? 'a' : 'div'"
            :href="link.url || undefined"
            :target="link.url ? '_blank' : undefined"
            :rel="link.url ? 'noopener noreferrer' : undefined"
            class="block w-full transition-all duration-300"
            :class="[
                cardClasses,
                settings.animation_hover === 'lift' ? 'hover:-translate-y-1' : '',
                settings.animation_hover === 'glow' && !glowSettings.enabled ? 'hover:shadow-2xl' : '',
                settings.animation_hover === 'pulse' ? 'hover:animate-pulse' : '',
                settings.animation_hover === 'shake' ? 'hover:animate-shake' : '',
                link.url ? 'cursor-pointer' : 'cursor-default',
                getCardLayoutClass(link.image_position),
            ]"
            :style="{
                ...cardStyle,
                backgroundColor: link.button_color || cardStyle.backgroundColor,
                color: link.text_color || settings.text_color,
            }"
            @click="handleClick"
        >
            <!-- Image Top (Prominent) -->
            <div
                v-if="(link.image || link.thumbnail) && link.image_position === 'top'"
                class="w-full overflow-hidden"
                :class="[
                    getImageShapeClass(link.image_shape),
                    'rounded-t-lg',
                ]"
            >
                <img
                    :src="link.image ? `/storage/${link.image}` : (link.thumbnail ? `/storage/${link.thumbnail}` : '')"
                    :alt="link.title"
                    class="w-full h-48 sm:h-56 object-cover"
                />
            </div>

            <!-- Content Container -->
            <div
                class="flex items-center gap-3"
                :class="{
                    'flex-row': link.image_position === 'left' || (!link.image_position && (link.image || link.thumbnail)),
                    'flex-row-reverse': link.image_position === 'right',
                    'flex-col': link.image_position === 'top' || link.image_position === 'bottom',
                    'p-4': link.image_position !== 'top' && link.image_position !== 'bottom',
                    'p-4 pt-0': link.image_position === 'top',
                    'p-4 pb-0': link.image_position === 'bottom',
                }"
            >
                <!-- Image Left -->
                <div
                    v-if="(link.image || link.thumbnail) && (link.image_position === 'left' || (!link.image_position && (link.image || link.thumbnail)))"
                    class="flex-shrink-0 overflow-hidden"
                    :class="[
                        'h-12 w-12 sm:h-14 sm:w-14',
                        getImageShapeClass(link.image_shape),
                    ]"
                >
                    <img
                        :src="link.image ? `/storage/${link.image}` : (link.thumbnail ? `/storage/${link.thumbnail}` : '')"
                        :alt="link.title"
                        class="h-full w-full object-cover"
                    />
                </div>

                <!-- Text Content -->
                <div
                    class="flex-1 min-w-0 text-left"
                    :class="{
                        'text-center': link.image_position === 'top' || link.image_position === 'bottom',
                    }"
                >
                    <p class="font-semibold">{{ link.title }}</p>
                    <p v-if="link.description" class="text-sm opacity-70 mt-1">{{ link.description }}</p>
                </div>

                <!-- Image Right -->
                <div
                    v-if="(link.image || link.thumbnail) && link.image_position === 'right'"
                    class="flex-shrink-0 overflow-hidden"
                    :class="[
                        'h-12 w-12 sm:h-14 sm:w-14',
                        getImageShapeClass(link.image_shape),
                    ]"
                >
                    <img
                        :src="link.image ? `/storage/${link.image}` : (link.thumbnail ? `/storage/${link.thumbnail}` : '')"
                        :alt="link.title"
                        class="h-full w-full object-cover"
                    />
                </div>

                <!-- Chevron (only if has URL) -->
                <ChevronRight
                    v-if="link.url"
                    class="w-5 h-5 opacity-50 flex-shrink-0"
                    :class="{
                        'hidden': link.image_position === 'top' || link.image_position === 'bottom',
                    }"
                />
            </div>

            <!-- Image Bottom (Prominent) -->
            <div
                v-if="(link.image || link.thumbnail) && link.image_position === 'bottom'"
                class="w-full overflow-hidden"
                :class="[
                    getImageShapeClass(link.image_shape),
                    'rounded-b-lg',
                ]"
            >
                <img
                    :src="link.image ? `/storage/${link.image}` : (link.thumbnail ? `/storage/${link.thumbnail}` : '')"
                    :alt="link.title"
                    class="w-full h-48 sm:h-56 object-cover"
                />
            </div>
        </component>
    </component>
</template>

