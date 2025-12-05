<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\ProfileSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(Request $request): Response
    {
        $organizations = $request->user()
            ->organizations()
            ->withCount(['profiles', 'products'])
            ->orderBy('created_at', 'desc')
            ->get();

        $limits = $request->user()->getLimits();

        return Inertia::render('Admin/Organizations/Index', [
            'organizations' => $organizations,
            'canCreate' => $request->user()->canCreateOrganization(),
            'limits' => [
                'max' => $limits->max_organizations,
                'current' => $organizations->count(),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        if (!$request->user()->canCreateOrganization()) {
            return redirect()->route('admin.organizations.index')
                ->with('error', 'Has alcanzado el límite de organizaciones.');
        }

        return Inertia::render('Admin/Organizations/Create');
    }

    public function store(Request $request)
    {
        if (!$request->user()->canCreateOrganization()) {
            return back()->with('error', 'Has alcanzado el límite de organizaciones.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:organizations,slug'],
            'type' => ['required', 'in:business,personal'],
            'description' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $validated['user_id'] = $request->user()->id;
        $validated['is_active'] = true;

        $organization = Organization::create($validated);

        // Create primary profile automatically
        $profile = Profile::create([
            'organization_id' => $organization->id,
            'user_id' => $request->user()->id,
            'name' => $organization->name,
            'is_primary' => true,
            'is_active' => true,
        ]);

        // Create default settings for the profile
        ProfileSetting::create([
            'profile_id' => $profile->id,
            ...ProfileSetting::getDefaults(),
        ]);

        return redirect()->route('admin.organizations.edit', $organization)
            ->with('success', '¡Organización creada exitosamente!');
    }

    public function edit(Request $request, Organization $organization): Response
    {
        $this->authorize('update', $organization);

        $organization->load([
            'profiles' => function ($query) {
                $query->with(['settings', 'socialLinks', 'customLinks', 'floatingAlerts', 'vcardSettings'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('created_at', 'asc');
            },
            'productCategories',
            'products.category',
        ]);

        $limits = $request->user()->getLimits();

        return Inertia::render('Admin/Organizations/Edit', [
            'organization' => $organization,
            'limits' => [
                'max_profiles' => $limits->max_profiles_per_org,
                'max_products' => $limits->max_products_per_org,
                'max_custom_links' => $limits->max_custom_links_per_profile,
                'max_social_links' => $limits->max_social_links_per_profile,
                'max_alerts' => $limits->max_alerts_per_profile,
                'can_upload_images' => $limits->can_upload_images,
                'can_remove_branding' => $limits->can_remove_branding,
            ],
            'socialPlatforms' => \App\Models\SocialLink::PLATFORMS,
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('organizations')->ignore($organization->id)],
            'type' => ['required', 'in:business,personal'],
            'description' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($organization->logo) {
                \Storage::disk('public')->delete($organization->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $organization->update($validated);

        return back()->with('success', '¡Cambios guardados!');
    }

    public function destroy(Request $request, Organization $organization)
    {
        $this->authorize('delete', $organization);

        // Soft delete
        $organization->delete();

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organización eliminada.');
    }

    /**
     * Generate a unique slug suggestion.
     */
    public function suggestSlug(Request $request)
    {
        $name = $request->input('name', '');
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return response()->json(['slug' => $slug]);
    }
}


