<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function store(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $limits = $request->user()->getLimits();
        if ($profile->socialLinks()->count() >= $limits->max_social_links_per_profile) {
            return back()->with('error', 'Has alcanzado el límite de redes sociales.');
        }

        $validated = $request->validate([
            'platform' => ['required', 'string'],
            'url' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:50'],
        ]);

        $validated['profile_id'] = $profile->id;
        $validated['sort_order'] = $profile->socialLinks()->max('sort_order') + 1;
        $validated['is_active'] = true;

        SocialLink::create($validated);

        return back()->with('success', '¡Red social agregada!');
    }

    public function update(Request $request, SocialLink $socialLink)
    {
        $this->authorize('update', $socialLink->profile->organization);

        $validated = $request->validate([
            'platform' => ['required', 'string'],
            'url' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $socialLink->update($validated);

        return back()->with('success', '¡Actualizado!');
    }

    public function destroy(SocialLink $socialLink)
    {
        $this->authorize('update', $socialLink->profile->organization);

        $socialLink->delete();

        return back()->with('success', '¡Eliminado!');
    }

    public function reorder(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:social_links,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($validated['items'] as $item) {
            SocialLink::where('id', $item['id'])
                ->where('profile_id', $profile->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return back()->with('success', '¡Orden actualizado!');
    }
}





