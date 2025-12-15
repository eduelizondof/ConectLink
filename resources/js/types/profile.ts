export interface SocialLink {
    id: number;
    platform: string;
    url: string;
    label: string;
    icon: string;
    brand_color: string;
}

export interface CustomLink {
    id: number;
    title: string;
    url?: string | null;
    description?: string;
    icon?: string;
    thumbnail?: string;
    image?: string;
    image_position?: 'left' | 'right' | 'top' | 'bottom' | null;
    image_shape?: 'square' | 'circle' | null;
    button_color?: string;
    text_color?: string;
    is_highlighted: boolean;
}

export interface FloatingAlert {
    id: number;
    title?: string;
    message: string;
    type: string;
    icon: string;
    link_url?: string;
    link_text?: string;
    position: string;
    animation: string;
    background_color: string;
    text_color?: string;
    is_dismissible: boolean;
    show_on_load: boolean;
    delay_seconds: number;
}

export interface ProfileSettings {
    background_type: string;
    background_color: string;
    background_gradient_start?: string;
    background_gradient_end?: string;
    background_gradient_direction: string;
    background_image?: string;
    background_overlay_opacity: number;
    background_animated_media?: string;
    background_animated_media_type?: 'image' | 'gif' | 'video';
    background_animated_overlay_opacity?: number;
    background_pattern?: string;
    background_pattern_opacity?: number;
    primary_color: string;
    secondary_color: string;
    text_color: string;
    text_secondary_color: string;
    card_style: string;
    card_background_color: string;
    card_border_radius: string;
    card_shadow: boolean;
    card_border_color?: string;
    card_glow_enabled?: boolean;
    card_glow_color?: string;
    card_glow_color_secondary?: string;
    card_glow_variant?: 'default' | 'cyan' | 'purple' | 'rainbow' | 'primary';
    card_glow_duration?: number;
    card_glow_opacity?: number;
    font_family: string;
    font_size: string;
    animation_entrance: string;
    animation_hover: string;
    animation_delay: number;
    show_particles?: boolean;
    particles_style?: string;
    particles_color?: string;
    layout_style: string;
    show_profile_photo: boolean;
    photo_style: string;
    photo_size: string;
    social_style: string;
    social_size: string;
    social_colored: boolean;
}

export interface Product {
    id: number;
    name: string;
    slug: string;
    short_description?: string;
    description?: string;
    price?: number;
    sale_price?: number;
    currency: string;
    image?: string;
    gallery?: string[];
    external_url?: string;
    whatsapp_message?: string;
    is_featured: boolean;
    is_available: boolean;
    category?: { id: number; name: string; slug: string };
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    products_count: number;
}

export interface ProductSection {
    id: number;
    title: string;
    slug: string;
    description?: string;
    icon?: string;
    products: Product[];
}

export interface Profile {
    id: number;
    name: string;
    slug?: string;
    photo?: string;
    job_title?: string;
    slogan?: string;
    bio?: string;
    is_primary: boolean;
    views_count: number;
    settings?: ProfileSettings;
    social_links: SocialLink[];
    custom_links: CustomLink[];
    floating_alerts: FloatingAlert[];
    vcard?: { is_active: boolean; full_name: string };
}

export interface Organization {
    id: number;
    name: string;
    slug: string;
    logo?: string;
    logo_url?: string;
    type: string;
    description?: string;
}

