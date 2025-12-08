<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\VcardSetting;
use Illuminate\Http\Request;

class VcardController extends Controller
{
    public function update(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'organization' => ['nullable', 'string', 'max:200'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:200'],
            'email_work' => ['nullable', 'email', 'max:200'],
            'phone' => ['nullable', 'string', 'max:30'],
            'phone_work' => ['nullable', 'string', 'max:30'],
            'phone_mobile' => ['nullable', 'string', 'max:30'],
            'fax' => ['nullable', 'string', 'max:30'],
            'address_street' => ['nullable', 'string', 'max:200'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_state' => ['nullable', 'string', 'max:100'],
            'address_zip' => ['nullable', 'string', 'max:20'],
            'address_country' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'include_photo' => ['boolean'],
        ]);

        $profile->vcardSettings()->updateOrCreate(
            ['profile_id' => $profile->id],
            $validated
        );

        return back()->with('success', '¡vCard actualizada!');
    }
}





