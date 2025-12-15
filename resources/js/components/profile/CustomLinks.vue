<script setup lang="ts">
import CustomLinkItem from './CustomLinkItem.vue';
import type { CustomLink, ProfileSettings } from '@/types/profile';
import { useProfileSettings } from '@/composables/useProfileSettings';
import { trackClick } from '@/composables/useProfileHelpers';

const props = defineProps<{
    links: CustomLink[];
    settings: ProfileSettings;
    isLoaded: boolean;
}>();

const { glowSettings, cardClasses, cardStyle } = useProfileSettings(props.settings);

function handleLinkClick(linkId: number) {
    trackClick(linkId);
}
</script>

<template>
    <div
        v-if="links.length > 0"
        class="space-y-3 mb-8"
        :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
        style="transition: all 0.6s ease-out 0.2s"
    >
        <CustomLinkItem
            v-for="(link, index) in links"
            :key="link.id"
            :link="link"
            :settings="settings"
            :glow-settings="glowSettings"
            :card-classes="cardClasses"
            :card-style="cardStyle"
            :animation-delay="index * settings.animation_delay"
            @click="handleLinkClick"
        />
    </div>
</template>

