import { computed, type ComputedRef, unref, type MaybeRefOrGetter } from 'vue';
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
        card_glow_duration: 6,
        card_glow_opacity: 1.0,
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

export function useProfileSettings(profileSettings?: MaybeRefOrGetter<ProfileSettings>) {
    const settings = computed(() => {
        const value = typeof profileSettings === 'function' 
            ? profileSettings() 
            : unref(profileSettings);
        return value || getDefaultSettings();
    });

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

        // Shadow - ajustar según estilo de tarjeta
        if (s.card_shadow) {
            if (s.card_style === 'glass') {
                // Glass necesita sombra más pronunciada para destacar el efecto
                classes.push('shadow-xl');
            } else if (s.card_style === 'transparent') {
                // Transparent necesita sombra más suave
                classes.push('shadow-md');
            } else {
                // Solid usa sombra estándar
                classes.push('shadow-lg');
            }
        }

        return classes.join(' ');
    });

    // Helper function to convert hex color to rgba
    const hexToRgba = (hex: string, alpha: number): string => {
        // Remove # if present
        const cleanHex = hex.replace('#', '');
        
        // Check if it's a valid hex color (6 characters)
        if (cleanHex.length === 6 && /^[0-9A-Fa-f]{6}$/.test(cleanHex)) {
            const r = parseInt(cleanHex.substring(0, 2), 16);
            const g = parseInt(cleanHex.substring(2, 4), 16);
            const b = parseInt(cleanHex.substring(4, 6), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
        
        // Fallback: if not a valid hex, try to use the color as-is with opacity
        // This handles named colors like 'white', 'transparent', etc.
        return hex;
    };

    const cardStyle = computed(() => {
        const s = settings.value;
        const style: Record<string, string> = {};
        const bgColor = s.card_background_color || '#ffffff';

        // Aplicar estilos según el tipo de tarjeta
        if (s.card_style === 'solid') {
            // Estilo sólido: fondo completamente opaco
            style.backgroundColor = bgColor;
        } else if (s.card_style === 'transparent') {
            // Estilo transparente: fondo con opacidad reducida
            const rgbaColor = hexToRgba(bgColor, 0.3);
            style.backgroundColor = rgbaColor;
            // Borde sutil para definir la tarjeta
            if (!s.card_border_color) {
                const borderColor = hexToRgba(bgColor, 0.2);
                style.border = `1px solid ${borderColor}`;
            }
        } else if (s.card_style === 'glass') {
            // Estilo cristal: efecto glassmorphism
            const rgbaColor = hexToRgba(bgColor, 0.15);
            style.backgroundColor = rgbaColor;
            style.backdropFilter = 'blur(20px) saturate(180%)';
            style.WebkitBackdropFilter = 'blur(20px) saturate(180%)';
            // Borde sutil para el efecto glass
            if (!s.card_border_color) {
                style.border = `1px solid rgba(255, 255, 255, 0.2)`;
            }
        } else {
            // Fallback a sólido
            style.backgroundColor = bgColor;
        }

        // Aplicar color de borde personalizado si existe (sobrescribe el borde por defecto)
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
        enabled: settings.value.card_glow_enabled ?? false,
        color: settings.value.card_glow_color || settings.value.primary_color,
        colorSecondary: settings.value.card_glow_color_secondary || settings.value.secondary_color,
        variant: settings.value.card_glow_variant || 'primary',
        borderRadius: cardBorderRadiusValue.value,
        duration: settings.value.card_glow_duration ?? 6,
        opacity: settings.value.card_glow_opacity ?? 1.0,
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

