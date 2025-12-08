<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'slug',
        'photo',
        'job_title',
        'slogan',
        'bio',
        'is_primary',
        'is_active',
        'views_count',
    ];

    protected $appends = [
        'photo_url',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    /**
     * Get the organization this profile belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user associated with this profile (if any).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the settings for this profile.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(ProfileSetting::class);
    }

    /**
     * Get all social links for this profile.
     */
    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class)->orderBy('sort_order');
    }

    /**
     * Get active social links.
     */
    public function activeSocialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Get all custom links for this profile.
     */
    public function customLinks(): HasMany
    {
        return $this->hasMany(CustomLink::class)->orderBy('sort_order');
    }

    /**
     * Get active custom links.
     */
    public function activeCustomLinks(): HasMany
    {
        return $this->hasMany(CustomLink::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Get all floating alerts for this profile.
     */
    public function floatingAlerts(): HasMany
    {
        return $this->hasMany(FloatingAlert::class)->orderBy('sort_order');
    }

    /**
     * Get active floating alerts.
     */
    public function activeFloatingAlerts(): HasMany
    {
        return $this->hasMany(FloatingAlert::class)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->orderBy('sort_order');
    }

    /**
     * Get vCard settings for this profile.
     */
    public function vcardSettings(): HasOne
    {
        return $this->hasOne(VcardSetting::class);
    }

    /**
     * Get QR settings for this profile.
     */
    public function qrSettings(): HasOne
    {
        return $this->hasOne(QrSetting::class);
    }

    /**
     * Get all views for this profile.
     */
    public function views(): HasMany
    {
        return $this->hasMany(ProfileView::class);
    }

    /**
     * Scope to filter active profiles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter primary profiles.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to find by slug within an organization.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get the full URL for this profile.
     */
    public function getUrlAttribute(): string
    {
        $url = config('app.url') . '/' . $this->organization->slug;

        if (!$this->is_primary && $this->slug) {
            $url .= '/' . $this->slug;
        }

        return $url;
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Get the full URL for the profile's photo.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }

        return asset('storage/' . $this->photo);
    }
}

