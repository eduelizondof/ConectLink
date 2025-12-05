<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\ProfileSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $limits = $request->user()->getLimits();
        if ($organization->profiles()->count() >= $limits->max_profiles_per_org) {
            return back()->with('error', 'Has alcanzado el límite de perfiles para esta organización.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('profiles')->where('organization_id', $organization->id)],
            'job_title' => ['nullable', 'string', 'max:100'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('profiles', 'public');
        }

        $validated['organization_id'] = $organization->id;
        $validated['is_primary'] = false;
        $validated['is_active'] = true;

        $profile = Profile::create($validated);

        // Create default settings
        ProfileSetting::create([
            'profile_id' => $profile->id,
            ...ProfileSetting::getDefaults(),
        ]);

        return back()->with('success', '¡Perfil creado!');
    }

    public function update(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash', Rule::unique('profiles')->where('organization_id', $profile->organization_id)->ignore($profile->id)],
            'job_title' => ['nullable', 'string', 'max:100'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('photo')) {
            if ($profile->photo) {
                \Storage::disk('public')->delete($profile->photo);
            }
            $validated['photo'] = $request->file('photo')->store('profiles', 'public');
        }

        // Primary profiles don't have slug
        if ($profile->is_primary) {
            unset($validated['slug']);
        }

        $profile->update($validated);

        return back()->with('success', '¡Perfil actualizado!');
    }

    public function updateSettings(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            // Background
            'background_type' => ['required', 'in:solid,gradient,image'],
            'background_color' => ['nullable', 'string', 'max:7'],
            'background_gradient_start' => ['nullable', 'string', 'max:7'],
            'background_gradient_end' => ['nullable', 'string', 'max:7'],
            'background_gradient_direction' => ['nullable', 'in:to-b,to-r,to-br,to-bl'],
            'background_image' => ['nullable', 'image', 'max:4096'],
            'background_overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],

            // Colors
            'primary_color' => ['nullable', 'string', 'max:7'],
            'secondary_color' => ['nullable', 'string', 'max:7'],
            'text_color' => ['nullable', 'string', 'max:7'],
            'text_secondary_color' => ['nullable', 'string', 'max:7'],

            // Card
            'card_style' => ['nullable', 'in:solid,transparent,glass'],
            'card_background_color' => ['nullable', 'string', 'max:50'],
            'card_border_radius' => ['nullable', 'in:none,sm,md,lg,xl,2xl,full'],
            'card_shadow' => ['nullable', 'boolean'],
            'card_border_color' => ['nullable', 'string', 'max:50'],

            // Typography
            'font_family' => ['nullable', 'string', 'max:50'],
            'font_size' => ['nullable', 'in:sm,base,lg'],

            // Animations
            'animation_entrance' => ['nullable', 'in:none,fade,slide-up,slide-down,scale,bounce'],
            'animation_hover' => ['nullable', 'in:none,lift,glow,pulse,shake'],
            'animation_delay' => ['nullable', 'integer', 'min:0', 'max:500'],

            // Layout
            'layout_style' => ['nullable', 'in:centered,left,compact'],
            'show_profile_photo' => ['nullable', 'boolean'],
            'photo_style' => ['nullable', 'in:circle,rounded,square'],
            'photo_size' => ['nullable', 'in:sm,md,lg,xl'],

            // Social
            'social_style' => ['nullable', 'in:icons,buttons,pills'],
            'social_size' => ['nullable', 'in:sm,md,lg'],
            'social_colored' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('background_image')) {
            $settings = $profile->settings;
            if ($settings && $settings->background_image) {
                \Storage::disk('public')->delete($settings->background_image);
            }
            $validated['background_image'] = $request->file('background_image')->store('backgrounds', 'public');
        }

        $profile->settings()->updateOrCreate(
            ['profile_id' => $profile->id],
            $validated
        );

        return back()->with('success', '¡Configuración guardada!');
    }

    public function destroy(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        if ($profile->is_primary) {
            return back()->with('error', 'No puedes eliminar el perfil principal.');
        }

        $profile->delete();

        return back()->with('success', '¡Perfil eliminado!');
    }
}


