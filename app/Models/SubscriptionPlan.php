<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_quarterly',
        'price_semiannual',
        'price_annual',
        'currency',
        'max_organizations',
        'max_profiles_per_org',
        'max_products_per_org',
        'max_custom_links_per_profile',
        'max_social_links_per_profile',
        'max_alerts_per_profile',
        'can_use_custom_domain',
        'can_remove_branding',
        'can_access_analytics',
        'can_upload_images',
        'features',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_quarterly' => 'decimal:2',
            'price_semiannual' => 'decimal:2',
            'price_annual' => 'decimal:2',
            'max_organizations' => 'integer',
            'max_profiles_per_org' => 'integer',
            'max_products_per_org' => 'integer',
            'max_custom_links_per_profile' => 'integer',
            'max_social_links_per_profile' => 'integer',
            'max_alerts_per_profile' => 'integer',
            'can_use_custom_domain' => 'boolean',
            'can_remove_branding' => 'boolean',
            'can_access_analytics' => 'boolean',
            'can_upload_images' => 'boolean',
            'features' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /**
     * Get price for a billing cycle.
     */
    public function getPriceForCycle(string $cycle): float
    {
        return match ($cycle) {
            'monthly' => $this->price_monthly,
            'quarterly' => $this->price_quarterly,
            'semiannual' => $this->price_semiannual,
            'annual' => $this->price_annual,
            default => $this->price_monthly,
        };
    }

    /**
     * Get savings percentage for a cycle compared to monthly.
     */
    public function getSavingsPercentage(string $cycle): int
    {
        $monthlyTotal = match ($cycle) {
            'quarterly' => $this->price_monthly * 3,
            'semiannual' => $this->price_monthly * 6,
            'annual' => $this->price_monthly * 12,
            default => $this->price_monthly,
        };

        $actualPrice = $this->getPriceForCycle($cycle);

        if ($monthlyTotal <= 0) {
            return 0;
        }

        return (int) round((($monthlyTotal - $actualPrice) / $monthlyTotal) * 100);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}


