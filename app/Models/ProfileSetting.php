<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        // Background
        'background_type',
        'background_color',
        'background_gradient_start',
        'background_gradient_end',
        'background_gradient_direction',
        'background_image',
        'background_overlay_opacity',
        'background_animated_media',
        'background_animated_media_type',
        'background_animated_overlay_opacity',
        'background_pattern',
        'background_pattern_opacity',
        // Theme colors
        'primary_color',
        'secondary_color',
        'text_color',
        'text_secondary_color',
        // Card styling
        'card_style',
        'card_background_color',
        'card_border_radius',
        'card_shadow',
        'card_border_color',
        'card_glow_enabled',
        'card_glow_color',
        'card_glow_color_secondary',
        'card_glow_variant',
        // Typography
        'font_family',
        'font_size',
        // Animations
        'animation_entrance',
        'animation_hover',
        'animation_delay',
        // Visual effects
        'show_particles',
        'particles_style',
        'particles_color',
        // Layout
        'layout_style',
        'show_profile_photo',
        'photo_style',
        'photo_size',
        // Social links style
        'social_style',
        'social_size',
        'social_colored',
    ];

    protected function casts(): array
    {
        return [
            'background_overlay_opacity' => 'integer',
            'background_pattern_opacity' => 'integer',
            'card_shadow' => 'boolean',
            'card_glow_enabled' => 'boolean',
            'animation_delay' => 'integer',
            'show_profile_photo' => 'boolean',
            'social_colored' => 'boolean',
            'show_particles' => 'boolean',
        ];
    }

    /**
     * Get the profile this settings belongs to.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get CSS variables for the theme.
     */
    public function getCssVariablesAttribute(): array
    {
        return [
            '--primary-color' => $this->primary_color,
            '--secondary-color' => $this->secondary_color,
            '--text-color' => $this->text_color,
            '--text-secondary-color' => $this->text_secondary_color,
            '--card-background' => $this->card_background_color,
            '--card-border-radius' => $this->getCardBorderRadiusValue(),
            '--animation-delay' => $this->animation_delay . 'ms',
        ];
    }

    /**
     * Get the actual border radius value.
     */
    protected function getCardBorderRadiusValue(): string
    {
        return match ($this->card_border_radius) {
            'none' => '0',
            'sm' => '0.125rem',
            'md' => '0.375rem',
            'lg' => '0.5rem',
            'xl' => '0.75rem',
            '2xl' => '1rem',
            'full' => '9999px',
            default => '0.5rem',
        };
    }

    /**
     * Get background CSS styles.
     */
    public function getBackgroundStyleAttribute(): string
    {
        return match ($this->background_type) {
            'gradient' => "background: linear-gradient({$this->background_gradient_direction}, {$this->background_gradient_start}, {$this->background_gradient_end});",
            'image' => "background-image: url('{$this->background_image}'); background-size: cover; background-position: center;",
            'animated' => $this->background_animated_media 
                ? "background-image: url('{$this->background_animated_media}'); background-size: cover; background-position: center;"
                : "background-color: {$this->background_color};",
            default => "background-color: {$this->background_color};",
        };
    }

    /**
     * Get default settings array.
     */
    public static function getDefaults(): array
    {
        return [
            'background_type' => 'solid',
            'background_color' => '#ffffff',
            'background_pattern' => 'none',
            'background_pattern_opacity' => 10,
            'primary_color' => '#3b82f6',
            'secondary_color' => '#8b5cf6',
            'text_color' => '#1f2937',
            'text_secondary_color' => '#6b7280',
            'card_style' => 'solid',
            'card_background_color' => '#ffffff',
            'card_border_radius' => 'lg',
            'card_shadow' => true,
            'card_glow_enabled' => false,
            'card_glow_variant' => 'primary',
            'font_family' => 'Inter',
            'font_size' => 'base',
            'animation_entrance' => 'fade',
            'animation_hover' => 'lift',
            'animation_delay' => 100,
            'show_particles' => false,
            'particles_style' => 'dots',
            'layout_style' => 'centered',
            'show_profile_photo' => true,
            'photo_style' => 'circle',
            'photo_size' => 'lg',
            'social_style' => 'icons',
            'social_size' => 'md',
            'social_colored' => true,
        ];
    }
}

