<script setup lang="ts">
/**
 * GlowingBorder - Animated glowing border effect
 * 
 * Inspired by Cursor's glowing effect from Aceternity UI
 * Optimized for mobile - always visible, not just on hover
 */
import { computed } from 'vue';

interface Props {
    /** Primary color for the glow */
    color?: string;
    /** Secondary color for gradient effect */
    colorSecondary?: string;
    /** Border radius in CSS units */
    borderRadius?: string;
    /** Border width in pixels */
    borderWidth?: number;
    /** Animation duration in seconds */
    duration?: number;
    /** Glow blur amount in pixels */
    blur?: number;
    /** Glow opacity (0-1) */
    opacity?: number;
    /** Whether to disable the effect */
    disabled?: boolean;
    /** Preset variants */
    variant?: 'default' | 'cyan' | 'purple' | 'rainbow' | 'primary';
}

const props = withDefaults(defineProps<Props>(), {
    color: '',
    colorSecondary: '',
    borderRadius: '1rem',
    borderWidth: 2,
    duration: 6,
    blur: 8,
    opacity: 1,
    disabled: false,
    variant: 'default',
});

const colors = computed(() => {
    if (props.color) {
        return {
            primary: props.color,
            secondary: props.colorSecondary || props.color,
        };
    }

    switch (props.variant) {
        case 'cyan':
            return { primary: '#06b6d4', secondary: '#22d3ee' };
        case 'purple':
            return { primary: '#8b5cf6', secondary: '#a855f7' };
        case 'rainbow':
            return { primary: '#ff0080', secondary: '#00ffff' };
        case 'primary':
            return { primary: 'var(--glow-primary, #3b82f6)', secondary: 'var(--glow-secondary, #8b5cf6)' };
        default:
            return { primary: '#06b6d4', secondary: '#a855f7' };
    }
});

const cssVars = computed(() => ({
    '--glow-color-1': colors.value.primary,
    '--glow-color-2': colors.value.secondary,
    '--glow-border-radius': props.borderRadius,
    '--glow-border-width': `${props.borderWidth}px`,
    '--glow-duration': `${props.duration}s`,
    '--glow-blur': `${props.blur}px`,
    '--glow-opacity': props.opacity,
}));
</script>

<template>
    <div
        class="glowing-border-wrapper"
        :class="{ 'glowing-border-active': !disabled }"
        :style="cssVars"
    >
        <!-- Glow layers -->
        <div v-if="!disabled" class="glowing-border-glow" aria-hidden="true">
            <div class="glowing-border-gradient" />
        </div>
        
        <!-- Content container -->
        <div class="glowing-border-content">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.glowing-border-wrapper {
    position: relative;
    border-radius: var(--glow-border-radius);
    isolation: isolate;
}

.glowing-border-glow {
    position: absolute;
    inset: calc(var(--glow-border-width) * -1);
    border-radius: var(--glow-border-radius);
    pointer-events: none;
    overflow: hidden;
    opacity: var(--glow-opacity);
}

.glowing-border-gradient {
    position: absolute;
    inset: -50%;
    background: conic-gradient(
        from 0deg,
        var(--glow-color-1),
        var(--glow-color-2),
        var(--glow-color-1),
        var(--glow-color-2),
        var(--glow-color-1)
    );
    animation: glow-spin var(--glow-duration) linear infinite;
}

.glowing-border-glow::after {
    content: '';
    position: absolute;
    inset: var(--glow-border-width);
    border-radius: calc(var(--glow-border-radius) - var(--glow-border-width));
    background: inherit;
}

.glowing-border-content {
    position: relative;
    z-index: 1;
    border-radius: var(--glow-border-radius);
    overflow: hidden;
}

/* Optional blur glow effect */
.glowing-border-active .glowing-border-glow::before {
    content: '';
    position: absolute;
    inset: 0;
    background: inherit;
    filter: blur(var(--glow-blur));
    opacity: 0.5;
}

@keyframes glow-spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
