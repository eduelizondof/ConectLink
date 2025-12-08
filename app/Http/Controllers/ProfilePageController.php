<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Profile;
use App\Models\ProfileView;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfilePageController extends Controller
{
    /**
     * Display the organization's primary profile page.
     */
    public function showOrganization(Request $request, string $orgSlug): Response
    {
        $organization = Organization::query()
            ->where('slug', $orgSlug)
            ->where('is_active', true)
            ->with(['primaryProfile.settings', 'primaryProfile.activeSocialLinks', 'primaryProfile.activeCustomLinks', 'primaryProfile.activeFloatingAlerts', 'primaryProfile.vcardSettings'])
            ->firstOrFail();

        $profile = $organization->primaryProfile;

        if (!$profile || !$profile->is_active) {
            abort(404);
        }

        // Record view
        $this->recordProfileView($request, $profile);

        // Load products if exists
        $products = $organization->products()
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->with('category')
            ->get();

        $categories = $organization->productCategories()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->get();

        return Inertia::render('Public/ProfilePage', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
                'logo' => $organization->logo_url,
                'type' => $organization->type,
                'description' => $organization->description,
            ],
            'profile' => $this->formatProfile($profile),
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    /**
     * Display an employee's profile page.
     */
    public function showEmployee(Request $request, string $orgSlug, string $profileSlug): Response
    {
        $organization = Organization::query()
            ->where('slug', $orgSlug)
            ->where('is_active', true)
            ->firstOrFail();

        $profile = Profile::query()
            ->where('organization_id', $organization->id)
            ->where('slug', $profileSlug)
            ->where('is_active', true)
            ->with(['settings', 'activeSocialLinks', 'activeCustomLinks', 'activeFloatingAlerts', 'vcardSettings'])
            ->firstOrFail();

        // Record view
        $this->recordProfileView($request, $profile);

        // Load products (shared across organization)
        $products = $organization->products()
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->with('category')
            ->get();

        $categories = $organization->productCategories()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->get();

        return Inertia::render('Public/ProfilePage', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
                'logo' => $organization->logo_url,
                'type' => $organization->type,
                'description' => $organization->description,
            ],
            'profile' => $this->formatProfile($profile),
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    /**
     * Download vCard for a profile.
     */
    public function downloadVcard(Request $request, string $orgSlug, ?string $profileSlug = null)
    {
        $organization = Organization::query()
            ->where('slug', $orgSlug)
            ->where('is_active', true)
            ->firstOrFail();

        if ($profileSlug) {
            $profile = Profile::query()
                ->where('organization_id', $organization->id)
                ->where('slug', $profileSlug)
                ->where('is_active', true)
                ->with('vcardSettings')
                ->firstOrFail();
        } else {
            $profile = $organization->primaryProfile()
                ->where('is_active', true)
                ->with('vcardSettings')
                ->firstOrFail();
        }

        $vcardSettings = $profile->vcardSettings;

        if (!$vcardSettings || !$vcardSettings->is_active) {
            abort(404, 'vCard not available');
        }

        $vcardContent = $vcardSettings->generateVcard();
        $filename = str_replace(' ', '_', $profile->name) . '.vcf';

        return response($vcardContent)
            ->header('Content-Type', 'text/vcard')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Track link click.
     */
    public function trackClick(Request $request, int $linkId)
    {
        $link = \App\Models\CustomLink::findOrFail($linkId);
        $link->incrementClicks();

        return response()->json(['success' => true]);
    }

    /**
     * Format profile data for the frontend.
     */
    protected function formatProfile(Profile $profile): array
    {
        return [
            'id' => $profile->id,
            'name' => $profile->name,
            'slug' => $profile->slug,
            'photo' => $profile->photo_url,
            'job_title' => $profile->job_title,
            'slogan' => $profile->slogan,
            'bio' => $profile->bio,
            'is_primary' => $profile->is_primary,
            'views_count' => $profile->views_count,
            'settings' => $profile->settings?->toArray(),
            'social_links' => $profile->activeSocialLinks->map(fn($link) => [
                'id' => $link->id,
                'platform' => $link->platform,
                'url' => $link->url,
                'label' => $link->display_label,
                'icon' => $link->icon,
                'brand_color' => $link->brand_color,
            ]),
            'custom_links' => $profile->activeCustomLinks->map(fn($link) => [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
                'icon' => $link->icon,
                'thumbnail' => $link->thumbnail,
                'button_color' => $link->button_color,
                'text_color' => $link->text_color,
                'is_highlighted' => $link->is_highlighted,
            ]),
            'floating_alerts' => $profile->activeFloatingAlerts->map(fn($alert) => [
                'id' => $alert->id,
                'title' => $alert->title,
                'message' => $alert->message,
                'type' => $alert->type,
                'icon' => $alert->default_icon,
                'link_url' => $alert->link_url,
                'link_text' => $alert->link_text,
                'position' => $alert->position,
                'animation' => $alert->animation,
                'background_color' => $alert->default_color,
                'text_color' => $alert->text_color,
                'is_dismissible' => $alert->is_dismissible,
                'show_on_load' => $alert->show_on_load,
                'delay_seconds' => $alert->delay_seconds,
            ]),
            'vcard' => $profile->vcardSettings?->is_active ? [
                'is_active' => true,
                'full_name' => $profile->vcardSettings->full_name,
            ] : null,
        ];
    }

    /**
     * Record a profile view.
     */
    protected function recordProfileView(Request $request, Profile $profile): void
    {
        // Simple rate limiting: only count once per IP per hour
        $recentView = ProfileView::query()
            ->where('profile_id', $profile->id)
            ->where('ip_address', $request->ip())
            ->where('viewed_at', '>=', now()->subHour())
            ->exists();

        if (!$recentView) {
            ProfileView::recordView($profile, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
            ]);

            $profile->incrementViews();
        }
    }
}

