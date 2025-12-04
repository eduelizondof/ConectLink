<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 100 100"
        :class="className"
        v-bind="$attrs"
    >
        <defs>
            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#06b6d4"/>
                <stop offset="100%" style="stop-color:#0891b2"/>
            </linearGradient>
        </defs>
        <circle cx="50" cy="50" r="45" fill="url(#gradient)"/>
        <!-- Globe wireframe -->
        <g fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.9">
            <ellipse cx="50" cy="50" rx="35" ry="35"/>
            <ellipse cx="50" cy="50" rx="35" ry="15"/>
            <ellipse cx="50" cy="50" rx="35" ry="28" transform="rotate(90 50 50)"/>
            <ellipse cx="50" cy="50" rx="20" ry="35"/>
        </g>
        <!-- Connection dots -->
        <circle cx="30" cy="35" r="3" fill="currentColor"/>
        <circle cx="70" cy="40" r="3" fill="currentColor"/>
        <circle cx="50" cy="70" r="3" fill="currentColor"/>
        <circle cx="40" cy="55" r="2" fill="currentColor" opacity="0.7"/>
        <circle cx="65" cy="60" r="2" fill="currentColor" opacity="0.7"/>
        <!-- Connection lines -->
        <g stroke="currentColor" stroke-width="1" opacity="0.5">
            <line x1="30" y1="35" x2="50" y2="70"/>
            <line x1="70" y1="40" x2="50" y2="70"/>
            <line x1="30" y1="35" x2="70" y2="40"/>
        </g>
    </svg>
</template>
