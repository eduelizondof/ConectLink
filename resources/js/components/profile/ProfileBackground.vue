<script setup lang="ts">
import { computed } from 'vue';
import type { ProfileSettings } from '@/types/profile';
import { ProfileParticles } from '@/components/effects';
import { useProfileSettings } from '@/composables/useProfileSettings';

const props = defineProps<{
    settings: ProfileSettings;
}>();

const { backgroundStyle } = useProfileSettings(props.settings);
</script>

<template>
    <!-- Animated Background (Video/GIF/Image) -->
    <div
        v-if="settings.background_type === 'animated' && settings.background_animated_media"
        class="fixed inset-0 z-0 overflow-hidden"
    >
        <!-- Video Background -->
        <video
            v-if="settings.background_animated_media_type === 'video'"
            :src="`/storage/${settings.background_animated_media}`"
            autoplay
            loop
            muted
            playsinline
            class="absolute inset-0 w-full h-full object-cover"
        />
        <!-- GIF/Image Background -->
        <img
            v-else
            :src="`/storage/${settings.background_animated_media}`"
            alt="Background"
            class="absolute inset-0 w-full h-full object-cover"
        />
        <!-- Overlay for animated backgrounds -->
        <div
            v-if="(settings.background_animated_overlay_opacity || 0) > 0"
            class="absolute inset-0 bg-black pointer-events-none"
            :style="{ opacity: (settings.background_animated_overlay_opacity || 0) / 100 }"
        />
    </div>

    <!-- Background Pattern Overlay -->
    <div
        v-if="settings.background_pattern && settings.background_pattern !== 'none'"
        class="fixed inset-0 pointer-events-none z-0"
        :class="[`bg-pattern-${settings.background_pattern}`]"
        :style="{ opacity: (settings.background_pattern_opacity || 10) / 100 }"
    />

    <!-- Background Overlay (for static images) -->
    <div
        v-if="settings.background_type === 'image' && settings.background_overlay_opacity > 0"
        class="fixed inset-0 bg-black pointer-events-none z-0"
        :style="{ opacity: settings.background_overlay_opacity / 100 }"
    />

    <!-- Particles Effect -->
    <ProfileParticles
        v-if="settings.show_particles"
        :style="settings.particles_style as any"
        :color="settings.particles_color"
        :primary-color="settings.primary_color"
        :secondary-color="settings.secondary_color"
        :particle-count="40"
    />
</template>

<style scoped>
/* Background Patterns */
.bg-pattern-dots {
    background-image: radial-gradient(circle, currentColor 1px, transparent 1px);
    background-size: 20px 20px;
}

.bg-pattern-grid {
    background-image: 
        linear-gradient(to right, currentColor 1px, transparent 1px),
        linear-gradient(to bottom, currentColor 1px, transparent 1px);
    background-size: 30px 30px;
}

.bg-pattern-waves {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='currentColor' fill-opacity='1' d='M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,144C672,139,768,181,864,197.3C960,213,1056,203,1152,181.3C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
    background-size: cover;
    background-position: bottom;
}

.bg-pattern-noise {
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
}
</style>

