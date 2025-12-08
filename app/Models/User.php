<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get all organizations owned by this user.
     */
    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * Get all profiles associated with this user.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    /**
     * Get the user's limits.
     */
    public function limits(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserLimit::class);
    }

    /**
     * Get the user's subscriptions.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the user's active subscription.
     */
    public function activeSubscription(): ?\Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Subscription::class)->active()->latest();
    }

    /**
     * Check if user has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()->active()->exists();
    }

    /**
     * Get user limits (from subscription or defaults).
     */
    public function getLimits(): UserLimit
    {
        // Try to get limits from active subscription
        $activeSubscription = $this->subscriptions()->with('plan')->active()->first();
        
        if ($activeSubscription && $activeSubscription->plan) {
            $plan = $activeSubscription->plan;
            $limitData = [
                'max_organizations' => $plan->max_organizations,
                'max_profiles_per_org' => $plan->max_profiles_per_org,
                'max_products_per_org' => $plan->max_products_per_org,
                'max_custom_links_per_profile' => $plan->max_custom_links_per_profile,
                'max_social_links_per_profile' => $plan->max_social_links_per_profile,
                'max_alerts_per_profile' => $plan->max_alerts_per_profile,
                'can_use_custom_domain' => $plan->can_use_custom_domain,
                'can_remove_branding' => $plan->can_remove_branding,
                'can_access_analytics' => $plan->can_access_analytics,
                'can_upload_images' => $plan->can_upload_images,
            ];
            return new UserLimit($limitData);
        }
        
        // Fallback to user_limits table or defaults
        if ($this->limits) {
            return $this->limits;
        }
        
        return new UserLimit(UserLimit::getDefaults());
    }

    /**
     * Check if user can create more organizations.
     */
    public function canCreateOrganization(): bool
    {
        $limits = $this->getLimits();
        return $this->organizations()->count() < $limits->max_organizations;
    }

    /**
     * Check if user can create more profiles in an organization.
     */
    public function canCreateProfileIn(Organization $organization): bool
    {
        $limits = $this->getLimits();
        return $organization->profiles()->count() < $limits->max_profiles_per_org;
    }
}
