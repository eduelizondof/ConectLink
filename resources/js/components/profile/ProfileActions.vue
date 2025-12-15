<script setup lang="ts">
import { Download, Share2, Eye, Sparkles } from 'lucide-vue-next';
import type { Profile, Organization, ProfileSettings } from '@/types/profile';

const props = defineProps<{
    profile: Profile;
    organization: Organization;
    settings: ProfileSettings;
    isLoaded: boolean;
    currentUrl: string;
}>();

const emit = defineEmits<{
    'download-vcard': [];
    share: [];
}>();

function handleDownloadVcard() {
    emit('download-vcard');
}

function handleShare() {
    emit('share');
}
</script>

<template>
    <div
        class="flex gap-3 justify-center"
        :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
        style="transition: all 0.6s ease-out 0.4s"
    >
        <!-- Download vCard -->
        <button
            v-if="profile.vcard?.is_active"
            @click="handleDownloadVcard"
            class="flex items-center gap-2 px-5 py-3 rounded-xl font-semibold transition-all duration-300 hover:scale-105"
            :style="{ backgroundColor: settings.primary_color, color: '#ffffff' }"
        >
            <Download class="w-5 h-5" />
            Guardar Contacto
        </button>

        <!-- Share -->
        <button
            @click="handleShare"
            class="flex items-center gap-2 px-5 py-3 rounded-xl font-semibold transition-all duration-300 hover:scale-105"
            :style="{
                backgroundColor: `${settings.primary_color}20`,
                color: settings.primary_color,
                border: `1px solid ${settings.primary_color}`,
            }"
        >
            <Share2 class="w-5 h-5" />
            Compartir
        </button>
    </div>

    <!-- Views Counter -->
    <div class="text-center mt-8 opacity-50" :style="{ color: settings.text_secondary_color }">
        <div class="flex items-center justify-center gap-1 text-xs">
            <Eye class="w-3 h-3" />
            <span>{{ profile.views_count.toLocaleString() }} visitas</span>
        </div>
    </div>

    <!-- Powered By -->
    <div class="text-center mt-4">
        <a
            href="/"
            class="inline-flex items-center gap-1 text-xs opacity-40 hover:opacity-70 transition-opacity"
            :style="{ color: settings.text_secondary_color }"
        >
            <Sparkles class="w-3 h-3" />
            Creado con ConectLink
        </a>
    </div>
</template>

