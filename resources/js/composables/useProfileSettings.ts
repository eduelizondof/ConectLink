import { computed, type ComputedRef } from 'vue';
import type { ProfileSettings } from '@/types/profile';

export function getDefaultSettings(): ProfileSettings {
    return {
        background_type: 'solid',
        background_color: '#ffffff',
        background_gradient_direction: 'to-b',
        background_overlay_opacity: 0,
        background_pattern: 'none',
        background_pattern_opacity: 10,
        primary_color: '#3b82f6',
        secondary_color: '#8b5cf6',
        text_color: '#1f2937',
        text_secondary_color: '#6b7280',
        card_style: 'solid',
        card_background_color: '#ffffff',
        card_border_radius: 'lg',
        card_shadow: true,
        card_glow_enabled: false,
        card_glow_variant: 'primary',
        font_family: 'Inter',
        font_size: 'base',
        animation_entrance: 'fade',
        animation_hover: 'lift',
        animation_delay: 100,
        show_particles: false,
        particles_style: 'dots',
        layout_style: 'centered',
        show_profile_photo: true,
        photo_style: 'circle',
        photo_size: 'lg',
        social_style: 'icons',
        social_size: 'md',
        social_colored: true,
    };
}

export function getGradientDirection(direction: string): string {
    const map: Record<string, string> = {
        'to-b': '180deg',
        'to-r': '90deg',
        'to-br': '135deg',
        'to-bl': '225deg',
    };
    return map[direction] || '180deg';
}

export function useProfileSettings(profileSettings?: ProfileSettings) {
    const settings = computed(() => profileSettings || getDefaultSettings());

    const backgroundStyle = computed(() => {
        const s = settings.value;
        if (s.background_type === 'gradient') {
            return {
                background: `linear-gradient(${getGradientDirection(s.background_gradient_direction)}, ${s.background_gradient_start}, ${s.background_gradient_end})`,
            };
        } else if (s.background_type === 'image' && s.background_image) {
            return {
                backgroundImage: `url(${s.background_image})`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
            };
        } else if (s.background_type === 'animated' && s.background_animated_media) {
            // For animated backgrounds, return empty style as we'll use a video/img element
            return {};
        }
        return { backgroundColor: s.background_color };
    });

    const cardClasses = computed(() => {
        const s = settings.value;
        const classes = ['transition-all duration-300'];

        // Border radius
        const radiusMap: Record<string, string> = {
            none: 'rounded-none',
            sm: 'rounded-sm',
            md: 'rounded-md',
            lg: 'rounded-lg',
            xl: 'rounded-xl',
            '2xl': 'rounded-2xl',
            full: 'rounded-3xl',
        };
        classes.push(radiusMap[s.card_border_radius] || 'rounded-lg');

        // Shadow
        if (s.card_shadow) {
            classes.push('shadow-lg');
        }

        return classes.join(' ');
    });

    const cardStyle = computed(() => {
        const s = settings.value;
        const style: Record<string, string> = {
            backgroundColor: s.card_background_color,
        };

        if (s.card_style === 'glass') {
            style.backdropFilter = 'blur(20px)';
            style.WebkitBackdropFilter = 'blur(20px)';
        }

        if (s.card_border_color) {
            style.border = `1px solid ${s.card_border_color}`;
        }

        return style;
    });

    const photoClasses = computed(() => {
        const s = settings.value;
        const classes = ['overflow-hidden border-4', 'transition-transform duration-300'];

        // Style
        const styleMap: Record<string, string> = {
            circle: 'rounded-full',
            rounded: 'rounded-2xl',
            square: 'rounded-none',
        };
        classes.push(styleMap[s.photo_style] || 'rounded-full');

        // Size
        const sizeMap: Record<string, string> = {
            sm: 'w-20 h-20',
            md: 'w-28 h-28',
            lg: 'w-36 h-36',
            xl: 'w-44 h-44',
        };
        classes.push(sizeMap[s.photo_size] || 'w-36 h-36');

        return classes.join(' ');
    });

    const cardBorderRadiusValue = computed(() => {
        const radiusMap: Record<string, string> = {
            none: '0',
            sm: '0.25rem',
            md: '0.375rem',
            lg: '0.5rem',
            xl: '0.75rem',
            '2xl': '1rem',
            full: '1.5rem',
        };
        return radiusMap[settings.value.card_border_radius] || '0.5rem';
    });

    const glowSettings = computed(() => ({
        enabled: settings.value.card_glow_enabled || false,
        color: settings.value.card_glow_color || settings.value.primary_color,
        colorSecondary: settings.value.card_glow_color_secondary || settings.value.secondary_color,
        variant: settings.value.card_glow_variant || 'primary',
        borderRadius: cardBorderRadiusValue.value,
    }));

    return {
        settings,
        backgroundStyle,
        cardClasses,
        cardStyle,
        photoClasses,
        glowSettings,
        cardBorderRadiusValue,
        getGradientDirection,
    };
}

