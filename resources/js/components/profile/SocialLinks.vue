<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import type { SocialLink, ProfileSettings } from '@/types/profile';
import { useProfileHelpers } from '@/composables/useProfileHelpers';

const props = defineProps<{
    links: SocialLink[];
    settings: ProfileSettings;
    isLoaded: boolean;
}>();

const { getSocialIcon } = useProfileHelpers();
</script>

<template>
    <div
        v-if="links.length > 0"
        class="flex flex-wrap justify-center gap-3 mb-8"
        :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
        style="transition: all 0.6s ease-out 0.1s"
    >
        <!-- Icons Style -->
        <template v-if="settings.social_style === 'icons'">
            <a
                v-for="link in links"
                :key="link.id"
                :href="link.url"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center justify-center rounded-full transition-all duration-300 hover:scale-110"
                :class="{
                    'w-10 h-10': settings.social_size === 'sm',
                    'w-12 h-12': settings.social_size === 'md',
                    'w-14 h-14': settings.social_size === 'lg',
                }"
                :style="{
                    backgroundColor: settings.social_colored ? link.brand_color : settings.primary_color,
                    color: '#ffffff',
                }"
                :title="link.label"
            >
                <font-awesome-icon :icon="getSocialIcon(link.platform)" class="w-5 h-5" />
            </a>
        </template>

        <!-- Buttons Style -->
        <template v-else-if="settings.social_style === 'buttons'">
            <a
                v-for="link in links"
                :key="link.id"
                :href="link.url"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-300 hover:scale-105"
                :style="{
                    backgroundColor: settings.social_colored ? link.brand_color : settings.primary_color,
                    color: '#ffffff',
                }"
            >
                <font-awesome-icon :icon="getSocialIcon(link.platform)" class="w-4 h-4" />
                <span class="text-sm font-medium">{{ link.label }}</span>
            </a>
        </template>

        <!-- Pills Style -->
        <template v-else>
            <a
                v-for="link in links"
                :key="link.id"
                :href="link.url"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center gap-2 px-4 py-2 rounded-full transition-all duration-300 hover:scale-105"
                :style="{
                    backgroundColor: settings.social_colored ? `${link.brand_color}20` : `${settings.primary_color}20`,
                    color: settings.social_colored ? link.brand_color : settings.primary_color,
                    border: `1px solid ${settings.social_colored ? link.brand_color : settings.primary_color}`,
                }"
            >
                <font-awesome-icon :icon="getSocialIcon(link.platform)" class="w-4 h-4" />
                <span class="text-sm font-medium">{{ link.label }}</span>
            </a>
        </template>
    </div>
</template>

