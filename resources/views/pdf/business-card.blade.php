<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarjeta de Presentación - {{ $profile->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 3.5in 2in;
            margin: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: {{ $profileSettings?->text_color ?? '#1f2937' }};
        }

        .card {
            width: 3.5in;
            height: 2in;
            page-break-after: always;
            position: relative;
            overflow: hidden;
        }

        /* Front of card */
        .card-front {
            background-color: {{ $profileSettings?->card_background_color ?? '#ffffff' }};
            @if($profileSettings?->background_type === 'gradient')
                background: linear-gradient(
                    {{ $profileSettings->background_gradient_direction ?? '135deg' }},
                    {{ $profileSettings->background_gradient_start ?? '#ffffff' }},
                    {{ $profileSettings->background_gradient_end ?? '#f3f4f6' }}
                );
            @elseif($profileSettings?->background_type === 'solid')
                background-color: {{ $profileSettings->background_color ?? '#ffffff' }};
            @endif
            padding: 0.2in;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: {{ isset($isPreview) && $isPreview ? '12px' : '0' }};
        }

        .logo-container {
            width: 0.6in;
            height: 0.6in;
            margin-bottom: 0.1in;
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .company-name {
            font-size: 12pt;
            font-weight: 700;
            color: {{ $profileSettings?->text_color ?? '#1f2937' }};
            margin-bottom: 0.05in;
        }

        .profile-name {
            font-size: 10pt;
            font-weight: 600;
            color: {{ $profileSettings?->primary_color ?? '#3b82f6' }};
            margin-bottom: 0.03in;
        }

        .job-title {
            font-size: 8pt;
            color: {{ $profileSettings?->text_secondary_color ?? '#6b7280' }};
            margin-bottom: 0.08in;
        }

        .description {
            font-size: 7pt;
            color: {{ $profileSettings?->text_secondary_color ?? '#6b7280' }};
            max-width: 2.8in;
            line-height: 1.4;
        }

        /* Back of card */
        .card-back {
            background-color: {{ $profileSettings?->background_color ?? '#ffffff' }};
            @if($profileSettings?->background_type === 'gradient')
                background: linear-gradient(
                    {{ $profileSettings->background_gradient_direction ?? '135deg' }},
                    {{ $profileSettings->background_gradient_start ?? '#ffffff' }},
                    {{ $profileSettings->background_gradient_end ?? '#f3f4f6' }}
                );
            @endif
            padding: 0.15in;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: {{ isset($isPreview) && $isPreview ? '12px' : '0' }};
        }

        .qr-container {
            width: 1.2in;
            height: 1.2in;
            margin-bottom: 0.1in;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-placeholder {
            width: 100%;
            height: 100%;
            background: #f3f4f6;
            border: 2px dashed #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7pt;
            color: #9ca3af;
            border-radius: 8px;
        }

        .qr-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .social-links {
            display: flex;
            gap: 0.1in;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 3in;
        }

        .social-link {
            display: inline-flex;
            align-items: center;
            gap: 0.03in;
            font-size: 6pt;
            color: {{ $profileSettings?->text_secondary_color ?? '#6b7280' }};
            text-decoration: none;
        }

        .social-icon {
            width: 10px;
            height: 10px;
        }

        .profile-url {
            font-size: 7pt;
            color: {{ $profileSettings?->primary_color ?? '#3b82f6' }};
            margin-top: 0.08in;
        }

        /* Preview mode adjustments */
        @if(isset($isPreview) && $isPreview)
        .cards-container {
            display: flex;
            gap: 20px;
            padding: 20px;
            justify-content: center;
            flex-wrap: wrap;
            background: #f3f4f6;
            min-height: 100vh;
        }

        .card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        @endif
    </style>
</head>
<body>
    @if(isset($isPreview) && $isPreview)
    <div class="cards-container">
    @endif

    <!-- Front of Business Card -->
    <div class="card card-front">
        @if($organization->logo_url)
            <div class="logo-container">
                <img src="{{ $organization->logo_url }}" alt="{{ $organization->name }}">
            </div>
        @endif

        <div class="company-name">{{ $organization->name }}</div>

        @if(!$profile->is_primary)
            <div class="profile-name">{{ $profile->name }}</div>
        @endif

        @if($profile->job_title)
            <div class="job-title">{{ $profile->job_title }}</div>
        @endif

        @if($organization->description)
            <div class="description">{{ Str::limit($organization->description, 120) }}</div>
        @endif
    </div>

    <!-- Back of Business Card -->
    <div class="card card-back">
        <div class="qr-container">
            <!-- QR Code placeholder - will be replaced with actual QR in frontend -->
            <div class="qr-placeholder" id="qr-code">
                <span>QR Code</span>
            </div>
        </div>

        @if($socialLinks->count() > 0)
            <div class="social-links">
                @foreach($socialLinks->take(5) as $link)
                    <span class="social-link">
                        {{ $link->platform_config['label'] }}
                    </span>
                @endforeach
            </div>
        @endif

        <div class="profile-url">{{ str_replace(['https://', 'http://'], '', $profileUrl) }}</div>
    </div>

    @if(isset($isPreview) && $isPreview)
    </div>

    <script>
        // Pass QR settings to parent window for rendering
        window.qrData = {
            url: '{{ $profileUrl }}',
            settings: @json($qrSettings)
        };
    </script>
    @endif
</body>
</html>
