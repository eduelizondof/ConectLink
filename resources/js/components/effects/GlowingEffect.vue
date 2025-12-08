<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

interface Props {
    color?: string;
    colors?: string[];
    blur?: number;
    spread?: number;
    borderWidth?: number;
    borderRadius?: string;
    duration?: number;
    disabled?: boolean;
    variant?: 'default' | 'white' | 'primary' | 'rainbow';
}

const props = withDefaults(defineProps<Props>(), {
    color: '',
    colors: () => [],
    blur: 0,
    spread: 20,
    borderWidth: 2,
    borderRadius: '12px',
    duration: 3,
    disabled: false,
    variant: 'default',
});

const containerRef = ref<HTMLElement | null>(null);
const gradientPosition = ref(0);
let animationFrame: number;
let startTime: number;

const gradientColors = computed(() => {
    if (props.colors.length > 0) {
        return props.colors;
    }

    switch (props.variant) {
        case 'white':
            return ['#ffffff', '#a8a8a8', '#ffffff', '#a8a8a8', '#ffffff'];
        case 'primary':
            return ['#3b82f6', '#8b5cf6', '#ec4899', '#8b5cf6', '#3b82f6'];
        case 'rainbow':
            return ['#ff0000', '#ff8000', '#ffff00', '#00ff00', '#0080ff', '#8000ff', '#ff0080', '#ff0000'];
        default:
            return ['#00ffff', '#ff00ff', '#00ffff', '#ff00ff', '#00ffff'];
    }
});

const gradientStyle = computed(() => {
    const colors = gradientColors.value.join(', ');
    return `conic-gradient(from ${gradientPosition.value}deg, ${colors})`;
});

const containerStyle = computed(() => ({
    '--glow-border-width': `${props.borderWidth}px`,
    '--glow-border-radius': props.borderRadius,
    '--glow-blur': `${props.blur}px`,
    '--glow-spread': `${props.spread}px`,
    '--glow-duration': `${props.duration}s`,
}));

function animate(timestamp: number) {
    if (!startTime) startTime = timestamp;
    const elapsed = timestamp - startTime;
    
    // Calculate rotation based on duration
    const rotationSpeed = 360 / (props.duration * 1000);
    gradientPosition.value = (elapsed * rotationSpeed) % 360;
    
    animationFrame = requestAnimationFrame(animate);
}

onMounted(() => {
    if (!props.disabled) {
        animationFrame = requestAnimationFrame(animate);
    }
});

onUnmounted(() => {
    if (animationFrame) {
        cancelAnimationFrame(animationFrame);
    }
});

watch(() => props.disabled, (disabled) => {
    if (disabled) {
        cancelAnimationFrame(animationFrame);
    } else {
        animationFrame = requestAnimationFrame(animate);
    }
});
</script>

<template>
    <div
        ref="containerRef"
        class="glow-container relative"
        :style="containerStyle"
    >
        <!-- Glow effect layer -->
        <div
            v-if="!disabled"
            class="glow-effect absolute inset-0 pointer-events-none overflow-hidden"
            :style="{
                borderRadius: borderRadius,
                filter: blur > 0 ? `blur(${blur}px)` : undefined,
            }"
        >
            <!-- Animated gradient border -->
            <div
                class="absolute inset-[-50%] animate-spin-slow"
                :style="{
                    background: gradientStyle,
                    animationDuration: `${duration}s`,
                }"
            />
        </div>
        
        <!-- Inner content with mask to create border effect -->
        <div
            class="glow-inner relative z-10 h-full w-full"
            :style="{
                borderRadius: borderRadius,
            }"
        >
            <slot />
        </div>
        
        <!-- Border mask -->
        <div
            v-if="!disabled"
            class="glow-mask absolute inset-0 pointer-events-none"
            :style="{
                borderRadius: borderRadius,
                padding: `${borderWidth}px`,
                background: 'linear-gradient(to right, transparent, transparent)',
                WebkitMask: `linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0)`,
                WebkitMaskComposite: 'xor',
                maskComposite: 'exclude',
            }"
        >
            <div
                class="absolute inset-[-100%]"
                :style="{
                    background: gradientStyle,
                }"
            />
        </div>
    </div>
</template>

<style scoped>
.glow-container {
    position: relative;
    isolation: isolate;
}

.glow-effect {
    z-index: 0;
}

@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    animation: spin-slow linear infinite;
}
</style>
