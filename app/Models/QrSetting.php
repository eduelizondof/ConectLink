<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        // Colors
        'foreground_color',
        'background_color',
        // Style
        'dot_style',
        'corner_style',
        'corner_color',
        // Logo
        'show_logo',
        'logo_source',
        'custom_logo',
        'logo_size',
        'logo_shape',
        'logo_background_color',
        'logo_background_enabled',
        'logo_margin',
        // Error correction
        'error_correction',
        // Size
        'size',
        // Gradient
        'use_gradient',
        'gradient_start_color',
        'gradient_end_color',
        'gradient_type',
        'gradient_rotation',
    ];

    protected function casts(): array
    {
        return [
            'show_logo' => 'boolean',
            'logo_size' => 'integer',
            'logo_background_enabled' => 'boolean',
            'logo_margin' => 'integer',
            'size' => 'integer',
            'use_gradient' => 'boolean',
            'gradient_rotation' => 'integer',
        ];
    }

    /**
     * Dot style options.
     */
    public const DOT_STYLES = [
        'square' => 'Cuadrado',
        'rounded' => 'Redondeado',
        'dots' => 'Puntos',
        'classy' => 'Clásico',
        'classy-rounded' => 'Clásico Redondeado',
        'extra-rounded' => 'Extra Redondeado',
    ];

    /**
     * Corner style options.
     */
    public const CORNER_STYLES = [
        'square' => 'Cuadrado',
        'rounded' => 'Redondeado',
        'extra-rounded' => 'Extra Redondeado',
    ];

    /**
     * Logo shape options.
     */
    public const LOGO_SHAPES = [
        'square' => 'Cuadrado',
        'rounded' => 'Redondeado',
        'circle' => 'Circular',
    ];

    /**
     * Logo source options.
     */
    public const LOGO_SOURCES = [
        'organization' => 'Logo de Organización',
        'profile' => 'Foto de Perfil',
        'custom' => 'Personalizado',
    ];

    /**
     * Error correction levels.
     */
    public const ERROR_CORRECTIONS = [
        'L' => 'Bajo (7%)',
        'M' => 'Medio (15%)',
        'Q' => 'Alto (25%)',
        'H' => 'Máximo (30%)',
    ];

    /**
     * Get the profile this QR setting belongs to.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the logo URL based on source.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->show_logo) {
            return null;
        }

        return match ($this->logo_source) {
            'organization' => $this->profile->organization->logo_url,
            'profile' => $this->profile->photo_url,
            'custom' => $this->custom_logo ? asset('storage/' . $this->custom_logo) : null,
            default => null,
        };
    }

    /**
     * Get the full path to the logo file.
     */
    public function getLogoPathAttribute(): ?string
    {
        if (!$this->show_logo) {
            return null;
        }

        return match ($this->logo_source) {
            'organization' => $this->profile->organization->logo
                ? storage_path('app/public/' . $this->profile->organization->logo)
                : null,
            'profile' => $this->profile->photo
                ? storage_path('app/public/' . $this->profile->photo)
                : null,
            'custom' => $this->custom_logo
                ? storage_path('app/public/' . $this->custom_logo)
                : null,
            default => null,
        };
    }

    /**
     * Get default settings array.
     */
    public static function getDefaults(): array
    {
        return [
            'foreground_color' => '#000000',
            'background_color' => '#FFFFFF',
            'dot_style' => 'square',
            'corner_style' => 'square',
            'corner_color' => null,
            'show_logo' => false,
            'logo_source' => 'organization',
            'custom_logo' => null,
            'logo_size' => 20,
            'logo_shape' => 'circle',
            'logo_background_color' => '#FFFFFF',
            'logo_background_enabled' => true,
            'logo_margin' => 5,
            'error_correction' => 'H',
            'size' => 300,
            'use_gradient' => false,
            'gradient_start_color' => null,
            'gradient_end_color' => null,
            'gradient_type' => 'linear',
            'gradient_rotation' => 0,
        ];
    }
}
