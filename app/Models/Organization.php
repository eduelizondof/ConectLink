<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'logo',
        'type',
        'description',
        'is_active',
        'is_verified',
    ];

    protected $appends = [
        'logo_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Get the owner of this organization.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all profiles for this organization.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    /**
     * Get the primary profile for this organization.
     */
    public function primaryProfile(): HasOne
    {
        return $this->hasOne(Profile::class)->where('is_primary', true);
    }

    /**
     * Get employee profiles (non-primary) for this organization.
     */
    public function employeeProfiles(): HasMany
    {
        return $this->hasMany(Profile::class)->where('is_primary', false);
    }

    /**
     * Get all product categories for this organization.
     */
    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    /**
     * Get all products for this organization.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all product sections for this organization.
     */
    public function productSections(): HasMany
    {
        return $this->hasMany(ProductSection::class);
    }

    /**
     * Scope to filter active organizations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to find by slug.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get the full URL for this organization's profile.
     */
    public function getUrlAttribute(): string
    {
        return config('app.url') . '/' . $this->slug;
    }

    /**
     * Get the full URL for the organization's logo.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        return asset('storage/' . $this->logo);
    }
}

