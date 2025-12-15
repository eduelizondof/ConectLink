<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\CustomLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomLinkController extends Controller
{
    public function store(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $limits = $request->user()->getLimits();
        if ($profile->customLinks()->count() >= $limits->max_custom_links_per_profile) {
            return back()->with('error', 'Has alcanzado el límite de tarjetas personalizadas.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'url' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:200'],
            'icon' => ['nullable', 'string', 'max:50'],
            'thumbnail' => ['nullable', 'image', 'max:1024'],
            'image' => ['nullable', 'image', 'max:2048'],
            'image_position' => ['nullable', 'in:left,right,top,bottom'],
            'image_shape' => ['nullable', 'in:square,circle'],
            'button_color' => ['nullable', 'string', 'max:50'],
            'text_color' => ['nullable', 'string', 'max:50'],
            'is_highlighted' => ['boolean'],
        ]);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('custom-links', 'public');
        }

        $validated['profile_id'] = $profile->id;
        $validated['sort_order'] = $profile->customLinks()->max('sort_order') + 1;
        $validated['is_active'] = true;

        CustomLink::create($validated);

        return back()->with('success', '¡Tarjeta agregada!');
    }

    public function update(Request $request, CustomLink $customLink)
    {
        $this->authorize('update', $customLink->profile->organization);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'url' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:200'],
            'icon' => ['nullable', 'string', 'max:50'],
            'thumbnail' => ['nullable', 'image', 'max:1024'],
            'image' => ['nullable', 'image', 'max:2048'],
            'image_position' => ['nullable', 'in:left,right,top,bottom'],
            'image_shape' => ['nullable', 'in:square,circle'],
            'button_color' => ['nullable', 'string', 'max:50'],
            'text_color' => ['nullable', 'string', 'max:50'],
            'is_highlighted' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($customLink->thumbnail) {
                Storage::disk('public')->delete($customLink->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        if ($request->hasFile('image')) {
            if ($customLink->image) {
                Storage::disk('public')->delete($customLink->image);
            }
            $validated['image'] = $request->file('image')->store('custom-links', 'public');
        }

        $customLink->update($validated);

        return back()->with('success', '¡Actualizado!');
    }

    public function destroy(CustomLink $customLink)
    {
        $this->authorize('update', $customLink->profile->organization);

        if ($customLink->thumbnail) {
            Storage::disk('public')->delete($customLink->thumbnail);
        }

        if ($customLink->image) {
            Storage::disk('public')->delete($customLink->image);
        }

        $customLink->delete();

        return back()->with('success', '¡Eliminado!');
    }

    public function reorder(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:custom_links,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($validated['items'] as $item) {
            CustomLink::where('id', $item['id'])
                ->where('profile_id', $profile->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return back()->with('success', '¡Orden actualizado!');
    }
}





