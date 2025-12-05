<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLimit extends Model
{
    protected $fillable = [
        'user_id',
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
    ];

    protected function casts(): array
    {
        return [
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
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default limits for new users.
     */
    public static function getDefaults(): array
    {
        return [
            'max_organizations' => 1,
            'max_profiles_per_org' => 5,
            'max_products_per_org' => 20,
            'max_custom_links_per_profile' => 10,
            'max_social_links_per_profile' => 15,
            'max_alerts_per_profile' => 3,
            'can_use_custom_domain' => false,
            'can_remove_branding' => false,
            'can_access_analytics' => true,
            'can_upload_images' => true,
        ];
    }
}


