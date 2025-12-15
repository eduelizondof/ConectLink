<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\ProfileSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

    public function create(Request $request)
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

        // Load all relationships - don't use refresh() as it resets loaded relations
        $organization->load([
            'profiles' => function ($query) {
                $query->with(['settings', 'socialLinks', 'customLinks', 'floatingAlerts', 'vcardSettings', 'qrSettings'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('created_at', 'asc');
            },
            'productCategories' => function ($query) {
                $query->orderBy('sort_order');
            },
            'productSections' => function ($query) {
                $query->with(['products' => function ($q) {
                    $q->orderByPivot('sort_order');
                }])->orderBy('sort_order');
            },
            'products.category',
            'products.sections',
        ]);

        $limits = $request->user()->getLimits();

        // DEBUG: Log settings being sent to frontend
        foreach ($organization->profiles as $profile) {
            if ($profile->settings) {
                Log::info('🔍 [OrganizationController] Profile settings being sent to frontend:', [
                    'profile_id' => $profile->id,
                    'profile_name' => $profile->name,
                    'card_shadow' => $profile->settings->card_shadow,
                    'card_shadow_type' => gettype($profile->settings->card_shadow),
                    'card_glow_enabled' => $profile->settings->card_glow_enabled,
                    'card_glow_enabled_type' => gettype($profile->settings->card_glow_enabled),
                    'card_glow_duration' => $profile->settings->card_glow_duration,
                    'card_glow_duration_type' => gettype($profile->settings->card_glow_duration),
                    'card_glow_opacity' => $profile->settings->card_glow_opacity,
                    'card_glow_opacity_type' => gettype($profile->settings->card_glow_opacity),
                    'settings_array' => $profile->settings->toArray(),
                ]);
            }
        }

        return Inertia::render('Admin/Organizations/Edit', [
            'organization' => $organization,
            'limits' => [
                'max_profiles' => $limits->max_profiles_per_org,
                'max_products' => $limits->max_products_per_org,
                'max_sections' => $limits->max_sections_per_org ?? 10,
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
        Log::info('Organization update request started', [
            'organization_id' => $organization->id,
            'user_id' => $request->user()?->id,
            'has_file' => $request->hasFile('logo'),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'is_inertia' => $request->header('X-Inertia'),
        ]);

        $this->authorize('update', $organization);

        try {
            Log::info('Starting validation', [
                'request_data' => $request->except(['logo']),
                'has_logo_file' => $request->hasFile('logo'),
            ]);

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('organizations')->ignore($organization->id)],
                'type' => ['required', 'in:business,personal'],
                'description' => ['nullable', 'string', 'max:500'],
                'logo' => ['nullable', 'image', 'max:2048'],
                'is_active' => ['boolean'],
            ]);

            Log::info('Validation passed', ['validated_keys' => array_keys($validated)]);

            // Handle logo upload
            // Only process logo if it's actually a file upload
            if ($request->hasFile('logo')) {
                Log::info('Processing logo upload', [
                    'file_name' => $request->file('logo')->getClientOriginalName(),
                    'file_size' => $request->file('logo')->getSize(),
                ]);

                // Delete old logo if exists
                if ($organization->logo) {
                    Storage::disk('public')->delete($organization->logo);
                    Log::info('Deleted old logo', ['old_logo' => $organization->logo]);
                }
                $validated['logo'] = $request->file('logo')->store('logos', 'public');
                Log::info('Logo stored', ['new_logo_path' => $validated['logo']]);
            } else {
                Log::info('No logo file in request');
                // Remove logo from validated data if no file was uploaded
                // This ensures we don't accidentally set logo to null
                if (isset($validated['logo'])) {
                    unset($validated['logo']);
                }
            }

            Log::info('Updating organization', ['data_to_update' => array_keys($validated)]);
            $organization->update($validated);
            Log::info('Organization updated successfully');

            return back()->with('success', '¡Cambios guardados!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'organization_id' => $organization->id,
            ]);
            // Return validation errors properly for Inertia
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating organization', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'organization_id' => $organization->id,
                'user_id' => $request->user()?->id,
                'exception' => get_class($e),
            ]);

            return back()->with('error', 'Error al guardar los cambios. Por favor, intenta de nuevo.');
        }
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





