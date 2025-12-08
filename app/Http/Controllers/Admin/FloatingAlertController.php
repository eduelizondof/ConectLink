<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\FloatingAlert;
use Illuminate\Http\Request;

class FloatingAlertController extends Controller
{
    public function store(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $limits = $request->user()->getLimits();
        if ($profile->floatingAlerts()->count() >= $limits->max_alerts_per_profile) {
            return back()->with('error', 'Has alcanzado el límite de alertas.');
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:300'],
            'type' => ['required', 'in:info,promo,warning,success,announcement'],
            'icon' => ['nullable', 'string', 'max:50'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'link_text' => ['nullable', 'string', 'max:50'],
            'position' => ['required', 'in:top,bottom,top-left,top-right,bottom-left,bottom-right'],
            'animation' => ['required', 'in:none,bounce,pulse,shake,slide'],
            'background_color' => ['nullable', 'string', 'max:50'],
            'text_color' => ['nullable', 'string', 'max:50'],
            'is_dismissible' => ['boolean'],
            'show_on_load' => ['boolean'],
            'delay_seconds' => ['nullable', 'integer', 'min:0', 'max:30'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $validated['profile_id'] = $profile->id;
        $validated['sort_order'] = $profile->floatingAlerts()->max('sort_order') + 1;
        $validated['is_active'] = true;

        FloatingAlert::create($validated);

        return back()->with('success', '¡Alerta creada!');
    }

    public function update(Request $request, FloatingAlert $floatingAlert)
    {
        $this->authorize('update', $floatingAlert->profile->organization);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:300'],
            'type' => ['required', 'in:info,promo,warning,success,announcement'],
            'icon' => ['nullable', 'string', 'max:50'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'link_text' => ['nullable', 'string', 'max:50'],
            'position' => ['required', 'in:top,bottom,top-left,top-right,bottom-left,bottom-right'],
            'animation' => ['required', 'in:none,bounce,pulse,shake,slide'],
            'background_color' => ['nullable', 'string', 'max:50'],
            'text_color' => ['nullable', 'string', 'max:50'],
            'is_dismissible' => ['boolean'],
            'show_on_load' => ['boolean'],
            'delay_seconds' => ['nullable', 'integer', 'min:0', 'max:30'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        $floatingAlert->update($validated);

        return back()->with('success', '¡Alerta actualizada!');
    }

    public function destroy(FloatingAlert $floatingAlert)
    {
        $this->authorize('update', $floatingAlert->profile->organization);

        $floatingAlert->delete();

        return back()->with('success', '¡Alerta eliminada!');
    }
}





