import {
    faFacebook,
    faInstagram,
    faTwitter,
    faLinkedin,
    faYoutube,
    faGithub,
    faTiktok,
    faWhatsapp,
    faTelegram,
    faPinterest,
    faSnapchat,
    faThreads,
    faDribbble,
    faBehance,
    faSpotify,
    faSoundcloud,
    faTwitch,
    faDiscord,
    faApple,
} from '@fortawesome/free-brands-svg-icons';
import { faGlobe, faEnvelope, faPhone, faLink } from '@fortawesome/free-solid-svg-icons';
import { AlertCircle, Info, CheckCircle, Tag, Megaphone } from 'lucide-vue-next';

export function getSocialIcon(platform: string) {
    const icons: Record<string, any> = {
        facebook: faFacebook,
        instagram: faInstagram,
        twitter: faTwitter,
        linkedin: faLinkedin,
        youtube: faYoutube,
        github: faGithub,
        tiktok: faTiktok,
        whatsapp: faWhatsapp,
        telegram: faTelegram,
        pinterest: faPinterest,
        snapchat: faSnapchat,
        threads: faThreads,
        dribbble: faDribbble,
        behance: faBehance,
        spotify: faSpotify,
        apple_music: faApple,
        soundcloud: faSoundcloud,
        twitch: faTwitch,
        discord: faDiscord,
        website: faGlobe,
        email: faEnvelope,
        phone: faPhone,
        other: faLink,
    };
    return icons[platform] || faLink;
}

export function getBrandColor(platform: string): string {
    const colors: Record<string, string> = {
        facebook: '#1877F2',
        instagram: '#E4405F',
        twitter: '#000000',
        tiktok: '#000000',
        linkedin: '#0A66C2',
        youtube: '#FF0000',
        whatsapp: '#25D366',
        telegram: '#0088CC',
        pinterest: '#BD081C',
        snapchat: '#FFFC00',
        threads: '#000000',
        github: '#181717',
        dribbble: '#EA4C89',
        behance: '#1769FF',
        spotify: '#1DB954',
        apple_music: '#FA243C',
        soundcloud: '#FF5500',
        twitch: '#9146FF',
        discord: '#5865F2',
        website: '#6B7280',
        email: '#6B7280',
        phone: '#6B7280',
        other: '#6B7280',
    };
    return colors[platform] || '#6B7280';
}

export function getAlertIcon(type: string) {
    const icons: Record<string, any> = {
        info: Info,
        promo: Tag,
        warning: AlertCircle,
        success: CheckCircle,
        announcement: Megaphone,
    };
    return icons[type] || Info;
}

export function getAlertPositionClasses(position: string): string {
    const positions: Record<string, string> = {
        top: 'top-4 left-1/2 -translate-x-1/2',
        bottom: 'bottom-4 left-1/2 -translate-x-1/2',
        'top-left': 'top-4 left-4',
        'top-right': 'top-4 right-4',
        'bottom-left': 'bottom-4 left-4',
        'bottom-right': 'bottom-4 right-4',
    };
    return positions[position] || 'bottom-4 right-4';
}

export function getAnimationClasses(animation: string): string {
    const animations: Record<string, string> = {
        bounce: 'animate-bounce',
        pulse: 'animate-pulse',
        shake: 'animate-shake',
        slide: 'animate-slide-in',
        none: '',
    };
    return animations[animation] || '';
}

export function getEntranceAnimationClass(animation: string): string {
    const animations: Record<string, string> = {
        fade: 'animate-fade-in',
        'slide-up': 'animate-slide-up',
        'slide-down': 'animate-slide-down',
        scale: 'animate-scale-in',
        bounce: 'animate-bounce-in',
        none: '',
    };
    return animations[animation] || '';
}

export function getImageShapeClass(shape: string | null | undefined): string {
    if (shape === 'circle') return 'rounded-full';
    return 'rounded-lg';
}

export function getCardLayoutClass(position: string | null | undefined): string {
    if (position === 'top' || position === 'bottom') {
        return 'text-center';
    }
    return '';
}

export function formatPrice(price: number, currency: string): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: currency,
    }).format(price);
}

export function trackClick(linkId: number) {
    fetch(`/api/links/${linkId}/click`, { method: 'POST' }).catch(() => {});
}

export function useProfileHelpers() {
    return {
        getSocialIcon,
        getBrandColor,
        getAlertIcon,
        getAlertPositionClasses,
        getAnimationClasses,
        getEntranceAnimationClass,
        getImageShapeClass,
        getCardLayoutClass,
        formatPrice,
        trackClick,
    };
}

