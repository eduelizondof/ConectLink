<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\QrSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QrBusinessCardController extends Controller
{
    /**
     * Update QR settings for a profile.
     */
    public function updateQrSettings(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile->organization);

        $validated = $request->validate([
            // Colors
            'foreground_color' => ['nullable', 'string', 'max:7', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'background_color' => ['nullable', 'string', 'max:7', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            // Style
            'dot_style' => ['nullable', 'in:square,rounded,dots,classy,classy-rounded,extra-rounded'],
            'corner_style' => ['nullable', 'in:square,rounded,extra-rounded'],
            'corner_color' => ['nullable', 'string', 'max:7', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            // Logo
            'show_logo' => ['boolean'],
            'logo_source' => ['nullable', 'in:organization,profile,custom'],
            'custom_logo' => ['nullable', 'image', 'max:2048'],
            'logo_size' => ['nullable', 'integer', 'min:10', 'max:40'],
            'logo_shape' => ['nullable', 'in:square,rounded,circle'],
            'logo_background_color' => ['nullable', 'string', 'max:7', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'logo_background_enabled' => ['boolean'],
            'logo_margin' => ['nullable', 'integer', 'min:0', 'max:20'],
            // Error correction
            'error_correction' => ['nullable', 'in:L,M,Q,H'],
            // Size
            'size' => ['nullable', 'integer', 'min:100', 'max:1000'],
            // Gradient
            'use_gradient' => ['boolean'],
            'gradient_start_color' => ['nullable', 'string', 'max:7', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'gradient_end_color' => ['nullable', 'string', 'max:7', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'gradient_type' => ['nullable', 'in:linear,radial'],
            'gradient_rotation' => ['nullable', 'integer', 'min:0', 'max:360'],
        ]);

        // Handle custom logo upload
        if ($request->hasFile('custom_logo')) {
            // Delete old custom logo if exists
            $existingSettings = $profile->qrSettings;
            if ($existingSettings && $existingSettings->custom_logo) {
                Storage::disk('public')->delete($existingSettings->custom_logo);
            }

            $validated['custom_logo'] = $request->file('custom_logo')->store(
                "organizations/{$profile->organization_id}/qr-logos",
                'public'
            );
        }

        $profile->qrSettings()->updateOrCreate(
            ['profile_id' => $profile->id],
            $validated
        );

        return back()->with('success', '¡Configuración del QR actualizada!');
    }

    /**
     * Get QR settings and configuration options.
     */
    public function getQrConfig(Profile $profile)
    {
        $this->authorize('view', $profile->organization);

        $settings = $profile->qrSettings ?? QrSetting::getDefaults();

        return response()->json([
            'settings' => $settings,
            'options' => [
                'dot_styles' => QrSetting::DOT_STYLES,
                'corner_styles' => QrSetting::CORNER_STYLES,
                'logo_shapes' => QrSetting::LOGO_SHAPES,
                'logo_sources' => QrSetting::LOGO_SOURCES,
                'error_corrections' => QrSetting::ERROR_CORRECTIONS,
            ],
            'profile_url' => $profile->url,
        ]);
    }

    /**
     * Generate QR code as base64 image (for preview and download).
     * This endpoint is used by the frontend to generate QR codes.
     */
    public function generateQr(Request $request, Profile $profile)
    {
        $settings = $profile->qrSettings ?? new QrSetting(QrSetting::getDefaults());
        $url = $profile->url;

        // Return QR configuration for frontend generation
        // The actual QR is generated in the browser using qrcode.js
        return response()->json([
            'url' => $url,
            'settings' => [
                'foreground_color' => $settings->foreground_color ?? '#000000',
                'background_color' => $settings->background_color ?? '#FFFFFF',
                'dot_style' => $settings->dot_style ?? 'square',
                'corner_style' => $settings->corner_style ?? 'square',
                'corner_color' => $settings->corner_color,
                'show_logo' => $settings->show_logo ?? false,
                'logo_url' => $settings->logo_url,
                'logo_size' => $settings->logo_size ?? 20,
                'logo_shape' => $settings->logo_shape ?? 'circle',
                'logo_background_color' => $settings->logo_background_color ?? '#FFFFFF',
                'logo_background_enabled' => $settings->logo_background_enabled ?? true,
                'logo_margin' => $settings->logo_margin ?? 5,
                'error_correction' => $settings->error_correction ?? 'H',
                'size' => $settings->size ?? 300,
                'use_gradient' => $settings->use_gradient ?? false,
                'gradient_start_color' => $settings->gradient_start_color,
                'gradient_end_color' => $settings->gradient_end_color,
                'gradient_type' => $settings->gradient_type ?? 'linear',
                'gradient_rotation' => $settings->gradient_rotation ?? 0,
            ],
        ]);
    }

    /**
     * Download QR code as PNG image.
     */
    public function downloadQr(Request $request, Profile $profile)
    {
        $format = $request->input('format', 'png');
        $size = $request->input('size', 300);

        // The download is handled by frontend since QR is generated there
        // This endpoint just provides the configuration
        return $this->generateQr($request, $profile);
    }

    /**
     * Generate business card PDF.
     */
    public function generateBusinessCard(Request $request, Profile $profile)
    {
        $this->authorize('view', $profile->organization);

        $profile->load(['organization', 'activeSocialLinks', 'qrSettings', 'settings']);

        $qrSettings = $profile->qrSettings ?? new QrSetting(QrSetting::getDefaults());

        // Get profile settings for colors
        $profileSettings = $profile->settings;

        $data = [
            'profile' => $profile,
            'organization' => $profile->organization,
            'socialLinks' => $profile->activeSocialLinks,
            'qrSettings' => $qrSettings,
            'profileSettings' => $profileSettings,
            'profileUrl' => $profile->url,
        ];

        $pdf = Pdf::loadView('pdf.business-card', $data);
        $pdf->setPaper([0, 0, 252, 144], 'landscape'); // Standard business card size (3.5" x 2")

        $filename = str_replace(' ', '_', $profile->name) . '_business_card.pdf';

        return $pdf->download($filename);
    }

    /**
     * Preview business card (returns HTML for iframe preview).
     */
    public function previewBusinessCard(Request $request, Profile $profile)
    {
        $this->authorize('view', $profile->organization);

        $profile->load(['organization', 'activeSocialLinks', 'qrSettings', 'settings']);

        $qrSettings = $profile->qrSettings ?? new QrSetting(QrSetting::getDefaults());
        $profileSettings = $profile->settings;

        return view('pdf.business-card', [
            'profile' => $profile,
            'organization' => $profile->organization,
            'socialLinks' => $profile->activeSocialLinks,
            'qrSettings' => $qrSettings,
            'profileSettings' => $profileSettings,
            'profileUrl' => $profile->url,
            'isPreview' => true,
        ]);
    }
}
