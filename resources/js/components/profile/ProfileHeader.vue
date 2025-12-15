<script setup lang="ts">
import type { Profile, Organization, ProfileSettings } from '@/types/profile';
import { useProfileSettings } from '@/composables/useProfileSettings';

const props = defineProps<{
    profile: Profile;
    organization: Organization;
    settings: ProfileSettings;
    isLoaded: boolean;
}>();

const { photoClasses } = useProfileSettings(props.settings);
</script>

<template>
    <div
        class="text-center mb-8"
        :class="{ 'opacity-0 translate-y-8': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
        style="transition: all 0.6s ease-out"
    >
        <!-- Photo -->
        <div v-if="settings.show_profile_photo && (profile.photo || organization.logo_url)" class="flex justify-center mb-4">
            <div :class="photoClasses" :style="{ borderColor: settings.primary_color }">
                <img
                    :src="profile.photo || organization.logo_url"
                    :alt="profile.name"
                    class="w-full h-full object-cover"
                />
            </div>
        </div>

        <!-- Name & Title -->
        <h1 class="text-2xl font-bold mb-1" :style="{ color: settings.text_color, fontFamily: settings.font_family }">
            {{ profile.name }}
        </h1>
        <p v-if="profile.job_title" class="text-sm mb-2" :style="{ color: settings.text_secondary_color }">
            {{ profile.job_title }}
        </p>

        <!-- Slogan -->
        <p v-if="profile.slogan" class="text-lg font-medium mb-4" :style="{ color: settings.text_color }">
            {{ profile.slogan }}
        </p>

        <!-- Bio -->
        <p v-if="profile.bio" class="text-sm leading-relaxed max-w-md mx-auto" :style="{ color: settings.text_secondary_color }">
            {{ profile.bio }}
        </p>
    </div>
</template>

