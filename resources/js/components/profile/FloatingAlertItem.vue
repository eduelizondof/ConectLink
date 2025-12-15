<script setup lang="ts">
import { X, ChevronRight } from 'lucide-vue-next';
import type { FloatingAlert } from '@/types/profile';
import { getAlertIcon, getAlertPositionClasses, getAnimationClasses } from '@/composables/useProfileHelpers';

const props = defineProps<{
    alert: FloatingAlert;
}>();

const emit = defineEmits<{
    dismiss: [alertId: number];
}>();

function handleDismiss() {
    emit('dismiss', props.alert.id);
}
</script>

<template>
    <component
        :is="alert.link_url ? 'a' : 'div'"
        :href="alert.link_url || undefined"
        :target="alert.link_url ? '_blank' : undefined"
        :rel="alert.link_url ? 'noopener noreferrer' : undefined"
        class="fixed z-50 max-w-sm mx-4 block"
        :class="[
            getAlertPositionClasses(alert.position),
            getAnimationClasses(alert.animation),
            alert.link_url ? 'cursor-pointer' : '',
        ]"
    >
        <div
            class="rounded-xl p-4 shadow-2xl flex items-start gap-3 transition-transform"
            :class="alert.link_url ? 'hover:scale-[1.02]' : ''"
            :style="{
                backgroundColor: alert.background_color,
                color: alert.text_color || '#ffffff',
            }"
        >
            <component :is="getAlertIcon(alert.type)" class="w-5 h-5 flex-shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
                <p v-if="alert.title" class="font-semibold text-sm mb-1">{{ alert.title }}</p>
                <p class="text-sm opacity-90">{{ alert.message }}</p>
                <span
                    v-if="alert.link_url && alert.link_text"
                    class="inline-flex items-center gap-1 text-sm font-semibold mt-2 hover:underline"
                >
                    {{ alert.link_text }}
                    <ChevronRight class="w-4 h-4" />
                </span>
            </div>
            <button
                v-if="alert.is_dismissible"
                @click.stop.prevent="handleDismiss"
                class="p-1 rounded-full hover:bg-white/20 transition-colors"
            >
                <X class="w-4 h-4" />
            </button>
        </div>
    </component>
</template>

