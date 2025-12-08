<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlansController extends Controller
{
    public function index(Request $request): Response
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'prices' => [
                        'monthly' => $plan->price_monthly,
                        'quarterly' => $plan->price_quarterly,
                        'semiannual' => $plan->price_semiannual,
                        'annual' => $plan->price_annual,
                    ],
                    'savings' => [
                        'quarterly' => $plan->getSavingsPercentage('quarterly'),
                        'semiannual' => $plan->getSavingsPercentage('semiannual'),
                        'annual' => $plan->getSavingsPercentage('annual'),
                    ],
                    'currency' => $plan->currency,
                    'features' => $plan->features,
                    'limits' => [
                        'organizations' => $plan->max_organizations,
                        'profiles' => $plan->max_profiles_per_org,
                        'products' => $plan->max_products_per_org,
                        'custom_links' => $plan->max_custom_links_per_profile,
                        'social_links' => $plan->max_social_links_per_profile,
                        'alerts' => $plan->max_alerts_per_profile,
                    ],
                    'capabilities' => [
                        'custom_domain' => $plan->can_use_custom_domain,
                        'remove_branding' => $plan->can_remove_branding,
                        'analytics' => $plan->can_access_analytics,
                        'upload_images' => $plan->can_upload_images,
                    ],
                    'is_featured' => $plan->is_featured,
                ];
            });

        $user = $request->user();
        $activeSubscription = $user->subscriptions()->with('plan')->active()->first();

        return Inertia::render('Admin/Plans/Index', [
            'plans' => $plans,
            'currentSubscription' => $activeSubscription ? [
                'plan_id' => $activeSubscription->plan_id,
                'plan_name' => $activeSubscription->plan->name,
                'billing_cycle' => $activeSubscription->billing_cycle,
                'status' => $activeSubscription->status,
                'ends_at' => $activeSubscription->ends_at?->format('d/m/Y'),
                'days_remaining' => $activeSubscription->daysRemaining(),
            ] : null,
        ]);
    }
}





