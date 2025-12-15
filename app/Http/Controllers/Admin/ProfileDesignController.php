<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileDesignController extends Controller
{
    /**
     * Update background settings for a profile.
     */
    public function updateBackground(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        try {
            $validated = $request->validate([
                'background_type' => ['required', 'in:solid,gradient,image'],
                'background_color' => ['nullable', 'string', 'max:20', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
                'background_gradient_start' => ['nullable', 'string', 'max:20', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
                'background_gradient_end' => ['nullable', 'string', 'max:20', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
                'background_gradient_direction' => ['nullable', 'in:to-b,to-r,to-br,to-bl'],
                'background_image' => ['nullable', 'image', 'max:4096'],
                'background_overlay_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],
                'background_pattern' => ['nullable', 'in:none,dots,grid,waves,noise'],
                'background_pattern_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            ]);

            // Handle background image upload
            if ($request->hasFile('background_image')) {
                $settings = $profile->settings;
                if ($settings && $settings->background_image) {
                    Storage::disk('public')->delete($settings->background_image);
                }
                $validated['background_image'] = $request->file('background_image')->store('backgrounds', 'public');
            } else {
                // Remove background_image from validated if no file was sent
                unset($validated['background_image']);
            }

            // Normalize color values (ensure they start with #)
            $colorFields = ['background_color', 'background_gradient_start', 'background_gradient_end'];
            foreach ($colorFields as $field) {
                if (isset($validated[$field]) && !empty($validated[$field])) {
                    $validated[$field] = str_starts_with($validated[$field], '#') 
                        ? $validated[$field] 
                        : '#' . $validated[$field];
                }
            }

            // Only include non-empty values
            $updateData = array_filter($validated, function ($value) {
                return $value !== null && $value !== '';
            });

            $this->updateProfileSettings($profile, $updateData);

            return back()->with('success', '¡Fondo actualizado!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Background validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['background_image']),
                'profile_id' => $profile->id,
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Error updating background', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'profile_id' => $profile->id,
            ]);
            return back()->with('error', 'Error al guardar el fondo. Por favor, intenta de nuevo.');
        }
    }

    /**
     * Update color settings for a profile.
     */
    public function updateColors(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            'primary_color' => ['required', 'string', 'max:7'],
            'secondary_color' => ['required', 'string', 'max:7'],
            'text_color' => ['required', 'string', 'max:7'],
            'text_secondary_color' => ['required', 'string', 'max:7'],
        ]);

        $this->updateProfileSettings($profile, $validated);

        return back()->with('success', '¡Colores actualizados!');
    }

    /**
     * Update card settings for a profile.
     */
    public function updateCards(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            'card_style' => ['required', 'in:solid,transparent,glass'],
            'card_background_color' => ['nullable', 'string', 'max:50'],
            'card_border_radius' => ['required', 'in:none,sm,md,lg,xl,2xl,full'],
            'card_shadow' => ['nullable'],
            'card_border_color' => ['nullable', 'string', 'max:50'],
            'card_glow_enabled' => ['nullable'],
            'card_glow_color' => ['nullable', 'string', 'max:7'],
            'card_glow_color_secondary' => ['nullable', 'string', 'max:7'],
            'card_glow_variant' => ['nullable', 'in:default,cyan,purple,rainbow,primary'],
        ]);

        // Convert booleans
        $validated['card_shadow'] = filter_var($validated['card_shadow'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['card_glow_enabled'] = filter_var($validated['card_glow_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $this->updateProfileSettings($profile, $validated);

        return back()->with('success', '¡Tarjetas actualizadas!');
    }

    /**
     * Update animation settings for a profile.
     */
    public function updateAnimations(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            'animation_entrance' => ['required', 'in:none,fade,slide-up,slide-down,scale,bounce'],
            'animation_hover' => ['required', 'in:none,lift,glow,pulse,shake'],
            'animation_delay' => ['required', 'integer', 'min:0', 'max:500'],
        ]);

        $this->updateProfileSettings($profile, $validated);

        return back()->with('success', '¡Animaciones actualizadas!');
    }

    /**
     * Update visual effects settings for a profile.
     */
    public function updateEffects(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            'show_particles' => ['nullable'],
            'particles_style' => ['nullable', 'in:dots,lines,confetti,snow'],
            'particles_color' => ['nullable', 'string', 'max:7'],
        ]);

        // Convert boolean
        $validated['show_particles'] = filter_var($validated['show_particles'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $this->updateProfileSettings($profile, $validated);

        return back()->with('success', '¡Efectos actualizados!');
    }

    /**
     * Update layout settings for a profile.
     */
    public function updateLayout(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            'font_family' => ['required', 'string', 'max:50'],
            'font_size' => ['nullable', 'in:sm,base,lg'],
            'layout_style' => ['nullable', 'in:centered,left,compact'],
            'show_profile_photo' => ['nullable'],
            'photo_style' => ['nullable', 'in:circle,rounded,square'],
            'photo_size' => ['nullable', 'in:sm,md,lg,xl'],
            'social_style' => ['nullable', 'in:icons,buttons,pills'],
            'social_size' => ['nullable', 'in:sm,md,lg'],
            'social_colored' => ['nullable'],
        ]);

        // Convert booleans
        $validated['show_profile_photo'] = filter_var($validated['show_profile_photo'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $validated['social_colored'] = filter_var($validated['social_colored'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $this->updateProfileSettings($profile, $validated);

        return back()->with('success', '¡Diseño actualizado!');
    }

    /**
     * Helper to update profile settings.
     */
    private function updateProfileSettings(Profile $profile, array $data): void
    {
        $profile->settings()->updateOrCreate(
            ['profile_id' => $profile->id],
            $data
        );
    }
}

