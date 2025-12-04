<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'platform',
        'url',
        'label',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Platform configuration with icons and brand colors.
     */
    public const PLATFORMS = [
        'facebook' => ['label' => 'Facebook', 'icon' => 'facebook', 'color' => '#1877F2'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'instagram', 'color' => '#E4405F'],
        'twitter' => ['label' => 'X (Twitter)', 'icon' => 'twitter', 'color' => '#000000'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'tiktok', 'color' => '#000000'],
        'linkedin' => ['label' => 'LinkedIn', 'icon' => 'linkedin', 'color' => '#0A66C2'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'youtube', 'color' => '#FF0000'],
        'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'whatsapp', 'color' => '#25D366'],
        'telegram' => ['label' => 'Telegram', 'icon' => 'telegram', 'color' => '#0088CC'],
        'pinterest' => ['label' => 'Pinterest', 'icon' => 'pinterest', 'color' => '#BD081C'],
        'snapchat' => ['label' => 'Snapchat', 'icon' => 'snapchat', 'color' => '#FFFC00'],
        'threads' => ['label' => 'Threads', 'icon' => 'threads', 'color' => '#000000'],
        'github' => ['label' => 'GitHub', 'icon' => 'github', 'color' => '#181717'],
        'dribbble' => ['label' => 'Dribbble', 'icon' => 'dribbble', 'color' => '#EA4C89'],
        'behance' => ['label' => 'Behance', 'icon' => 'behance', 'color' => '#1769FF'],
        'spotify' => ['label' => 'Spotify', 'icon' => 'spotify', 'color' => '#1DB954'],
        'apple_music' => ['label' => 'Apple Music', 'icon' => 'apple-music', 'color' => '#FA243C'],
        'soundcloud' => ['label' => 'SoundCloud', 'icon' => 'soundcloud', 'color' => '#FF5500'],
        'twitch' => ['label' => 'Twitch', 'icon' => 'twitch', 'color' => '#9146FF'],
        'discord' => ['label' => 'Discord', 'icon' => 'discord', 'color' => '#5865F2'],
        'website' => ['label' => 'Website', 'icon' => 'globe', 'color' => '#6B7280'],
        'email' => ['label' => 'Email', 'icon' => 'mail', 'color' => '#6B7280'],
        'phone' => ['label' => 'Phone', 'icon' => 'phone', 'color' => '#6B7280'],
        'other' => ['label' => 'Link', 'icon' => 'link', 'color' => '#6B7280'],
    ];

    /**
     * Get the profile this social link belongs to.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the platform configuration.
     */
    public function getPlatformConfigAttribute(): array
    {
        return self::PLATFORMS[$this->platform] ?? self::PLATFORMS['other'];
    }

    /**
     * Get the display label.
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->label ?? $this->platform_config['label'];
    }

    /**
     * Get the icon name.
     */
    public function getIconAttribute(): string
    {
        return $this->platform_config['icon'];
    }

    /**
     * Get the brand color.
     */
    public function getBrandColorAttribute(): string
    {
        return $this->platform_config['color'];
    }

    /**
     * Scope to filter active social links.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

