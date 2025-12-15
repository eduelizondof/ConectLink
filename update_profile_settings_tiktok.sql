-- UPDATE para profile_settings de creadora de contenido TikTok
-- Paleta de colores suaves y armoniosos con tonos pastel

UPDATE `profile_settings` 
SET 
    -- Background: Gradiente suave de rosa pastel a lavanda
    `background_type` = 'gradient',
    `background_color` = '#fff5f8',
    `background_gradient_start` = '#ffd6e8',  -- Rosa pastel suave
    `background_gradient_end` = '#e6d5ff',    -- Lavanda pastel suave
    `background_gradient_direction` = 'to-br', -- Diagonal suave (bottom-right)
    `background_overlay_opacity` = 0,
    `background_pattern` = 'dots',
    `background_pattern_opacity` = 15,  -- Más sutil
    
    -- Colores del tema: Tonos pastel armoniosos
    `primary_color` = '#ff9ec5',      -- Rosa coral suave
    `secondary_color` = '#c084fc',    -- Lavanda suave
    `text_color` = '#2d1b3d',         -- Gris morado oscuro suave (mejor legibilidad)
    `text_secondary_color` = '#6b5b73', -- Gris morado medio
    
    -- Card: Estilo glass con colores suaves
    `card_style` = 'glass',
    `card_background_color` = 'rgba(255, 255, 255, 0.85)', -- Glass más suave
    `card_border_radius` = 'xl',
    `card_shadow` = 1,
    `card_border_color` = 'rgba(255, 182, 193, 0.3)', -- Rosa muy suave
    `card_glow_enabled` = 1,
    `card_glow_variant` = 'primary',
    
    -- Typography: Mantener Inter pero asegurar legibilidad
    `font_family` = 'Inter',
    `font_size` = 'base',
    
    -- Animations: Suaves y elegantes
    `animation_entrance` = 'fade',
    `animation_hover` = 'lift',
    `animation_delay` = 100,
    
    -- Visual Effects: Partículas suaves
    `show_particles` = 1,
    `particles_style` = 'dots',
    `particles_color` = '#ffb3d9', -- Rosa suave para partículas
    
    -- Layout: Centrado y elegante
    `layout_style` = 'centered',
    `show_profile_photo` = 1,
    `photo_style` = 'circle',
    `photo_size` = 'xl',
    
    -- Social: Iconos con colores suaves
    `social_style` = 'icons',
    `social_size` = 'lg',
    `social_colored` = 1,
    
    `updated_at` = NOW()
WHERE `profile_id` = 4;
