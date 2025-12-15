<script setup lang="ts">
import { computed } from 'vue';
import {
    Facebook,
    Instagram,
    Twitter,
    Linkedin,
    Youtube,
    Github,
    Globe,
    Mail,
    Phone,
    Link2,
    MessageCircle,
    Send,
    ChevronRight,
    Download,
    Eye,
} from 'lucide-vue-next';

interface Profile {
    id: number;
    name: string;
    photo?: string;
    job_title?: string;
    slogan?: string;
    bio?: string;
    settings?: {
        background_type: string;
        background_color: string;
        background_gradient_start?: string;
        background_gradient_end?: string;
        background_gradient_direction: string;
        background_animated_media?: string;
        background_animated_media_type?: 'image' | 'gif' | 'video';
        background_animated_overlay_opacity?: number;
        primary_color: string;
        text_color: string;
        text_secondary_color: string;
        card_style: string;
        card_background_color: string;
        card_border_radius: string;
        photo_style: string;
    };
    social_links?: any[];
    custom_links?: any[];
}

interface Organization {
    name: string;
    logo?: string;
}

const props = defineProps<{
    profile: Profile;
    organization: Organization;
}>();

const settings = computed(() => ({
    background_type: 'solid',
    background_color: '#ffffff',
    background_gradient_start: '#3b82f6',
    background_gradient_end: '#8b5cf6',
    background_gradient_direction: 'to-b',
    background_animated_media: undefined,
    background_animated_media_type: undefined,
    background_animated_overlay_opacity: 0,
    primary_color: '#3b82f6',
    text_color: '#1f2937',
    text_secondary_color: '#6b7280',
    card_style: 'solid',
    card_background_color: '#ffffff',
    card_border_radius: 'lg',
    photo_style: 'circle',
    ...props.profile.settings,
}));

const backgroundStyle = computed(() => {
    const s = settings.value;
    if (s.background_type === 'gradient' && s.background_gradient_start && s.background_gradient_end) {
        const dir = s.background_gradient_direction === 'to-b' ? '180deg' :
                    s.background_gradient_direction === 'to-r' ? '90deg' :
                    s.background_gradient_direction === 'to-br' ? '135deg' : '180deg';
        return { background: `linear-gradient(${dir}, ${s.background_gradient_start}, ${s.background_gradient_end})` };
    } else if (s.background_type === 'animated' && s.background_animated_media) {
        // For animated backgrounds, return empty style as we'll use a video/img element
        return {};
    }
    return { backgroundColor: s.background_color };
});

const photoClass = computed(() => {
    const style = settings.value.photo_style;
    return style === 'circle' ? 'rounded-full' : style === 'rounded' ? 'rounded-2xl' : 'rounded-none';
});

const cardClass = computed(() => {
    const radius = settings.value.card_border_radius;
    return radius === 'none' ? 'rounded-none' :
           radius === 'sm' ? 'rounded-sm' :
           radius === 'md' ? 'rounded-md' :
           radius === 'lg' ? 'rounded-lg' :
           radius === 'xl' ? 'rounded-xl' :
           radius === '2xl' ? 'rounded-2xl' : 'rounded-3xl';
});

function getSocialIcon(platform: string) {
    const icons: Record<string, any> = {
        facebook: Facebook,
        instagram: Instagram,
        twitter: Twitter,
        linkedin: Linkedin,
        youtube: Youtube,
        github: Github,
        whatsapp: MessageCircle,
        telegram: Send,
        website: Globe,
        email: Mail,
        phone: Phone,
    };
    return icons[platform] || Link2;
}
</script>

<template>
    <div class="relative rounded-2xl border shadow-lg overflow-hidden" style="max-height: 600px;">
        <!-- Phone Frame -->
        <div class="absolute inset-x-0 top-0 h-6 bg-black rounded-t-2xl flex items-center justify-center z-10">
            <div class="w-20 h-4 bg-black rounded-full" />
        </div>

        <!-- Animated Background -->
        <div
            v-if="settings.background_type === 'animated' && settings.background_animated_media"
            class="absolute inset-0 z-0 overflow-hidden rounded-2xl"
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
            <!-- Overlay -->
            <div
                v-if="(settings.background_animated_overlay_opacity || 0) > 0"
                class="absolute inset-0 bg-black"
                :style="{ opacity: (settings.background_animated_overlay_opacity || 0) / 100 }"
            />
        </div>

        <!-- Content -->
        <div
            class="relative pt-6 overflow-y-auto z-10"
            :style="[backgroundStyle, { minHeight: '500px', maxHeight: '600px' }]"
        >
            <div class="px-4 py-6 text-center">
                <!-- Profile Photo -->
                <div class="flex justify-center mb-4">
                    <div
                        class="w-24 h-24 border-4 overflow-hidden"
                        :class="photoClass"
                        :style="{ borderColor: settings.primary_color }"
                    >
                        <img
                            v-if="profile.photo"
                            :src="`/storage/${profile.photo}`"
                            :alt="profile.name"
                            class="w-full h-full object-cover"
                        />
                        <img
                            v-else-if="organization.logo"
                            :src="`/storage/${organization.logo}`"
                            :alt="organization.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <span class="text-3xl font-bold text-gray-400">
                                {{ profile.name.charAt(0) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Name & Title -->
                <h2 class="text-lg font-bold" :style="{ color: settings.text_color }">
                    {{ profile.name }}
                </h2>
                <p v-if="profile.job_title" class="text-sm mt-1" :style="{ color: settings.text_secondary_color }">
                    {{ profile.job_title }}
                </p>

                <!-- Slogan -->
                <p v-if="profile.slogan" class="text-base mt-3 font-medium" :style="{ color: settings.text_color }">
                    {{ profile.slogan }}
                </p>

                <!-- Bio -->
                <p v-if="profile.bio" class="text-sm mt-3 leading-relaxed" :style="{ color: settings.text_secondary_color }">
                    {{ profile.bio.length > 150 ? profile.bio.substring(0, 150) + '...' : profile.bio }}
                </p>

                <!-- Social Links -->
                <div v-if="profile.social_links?.length" class="flex flex-wrap justify-center gap-2 mt-6">
                    <div
                        v-for="link in profile.social_links.slice(0, 6)"
                        :key="link.id"
                        class="w-10 h-10 rounded-full flex items-center justify-center"
                        :style="{ backgroundColor: settings.primary_color }"
                    >
                        <component :is="getSocialIcon(link.platform)" class="w-5 h-5 text-white" />
                    </div>
                </div>

                <!-- Custom Links -->
                <div v-if="profile.custom_links?.length" class="mt-6 space-y-2 px-2">
                    <component
                        :is="link.url ? 'a' : 'div'"
                        v-for="link in profile.custom_links.slice(0, 3)"
                        :key="link.id"
                        :href="link.url || undefined"
                        :target="link.url ? '_blank' : undefined"
                        :rel="link.url ? 'noopener noreferrer' : undefined"
                        class="p-3 shadow-sm flex items-center gap-3"
                        :class="[
                            cardClass,
                            link.url ? 'cursor-pointer hover:opacity-90' : 'cursor-default',
                        ]"
                        :style="{ backgroundColor: link.button_color || settings.card_background_color }"
                    >
                        <!-- Image Left -->
                        <div
                            v-if="(link.image || link.thumbnail) && (link.image_position === 'left' || (!link.image_position && (link.image || link.thumbnail)))"
                            class="flex-shrink-0 overflow-hidden"
                            :class="{
                                'h-8 w-8': true,
                                'rounded-lg': link.image_shape === 'square' || !link.image_shape,
                                'rounded-full': link.image_shape === 'circle',
                            }"
                        >
                            <img
                                :src="link.image ? `/storage/${link.image}` : (link.thumbnail ? `/storage/${link.thumbnail}` : '')"
                                :alt="link.title"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-medium truncate block" :style="{ color: link.text_color || settings.text_color }">
                                {{ link.title }}
                            </span>
                            <span v-if="link.description" class="text-xs opacity-70 truncate block" :style="{ color: link.text_color || settings.text_color }">
                                {{ link.description }}
                            </span>
                        </div>

                        <!-- Image Right -->
                        <div
                            v-if="(link.image || link.thumbnail) && link.image_position === 'right'"
                            class="flex-shrink-0 overflow-hidden"
                            :class="{
                                'h-8 w-8': true,
                                'rounded-lg': link.image_shape === 'square' || !link.image_shape,
                                'rounded-full': link.image_shape === 'circle',
                            }"
                        >
                            <img
                                :src="link.image ? `/storage/${link.image}` : (link.thumbnail ? `/storage/${link.thumbnail}` : '')"
                                :alt="link.title"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <!-- Chevron -->
                        <ChevronRight v-if="link.url" class="w-4 h-4 opacity-50 flex-shrink-0" :style="{ color: link.text_color || settings.text_color }" />
                    </component>
                </div>

                <!-- Download Contact Button -->
                <button
                    class="mt-6 inline-flex items-center gap-2 px-6 py-2 rounded-lg text-sm font-medium text-white"
                    :style="{ backgroundColor: settings.primary_color }"
                >
                    <Download class="w-4 h-4" />
                    Guardar Contacto
                </button>

                <!-- Views -->
                <div class="mt-4 flex items-center justify-center gap-1 text-xs opacity-50" :style="{ color: settings.text_secondary_color }">
                    <Eye class="w-3 h-3" />
                    <span>Vista previa</span>
                </div>
            </div>
        </div>
    </div>
</template>





