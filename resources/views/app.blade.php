<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
        
        {{-- SEO Meta Tags --}}
        <meta name="description" content="Link - La forma más fácil, moderna y profesional de compartir toda tu información, enlaces y catálogos desde un solo lugar. Tu tarjeta digital inteligente por CNVA - Conectiva IT Solutions.">
        <meta name="keywords" content="tarjeta digital, linktree, link in bio, tarjeta de presentación, QR, contacto digital, Link CNVA, marketing digital Guadalajara, presencia online, redes sociales, Conectiva IT Solutions">
        <meta name="author" content="CNVA - Conectiva IT Solutions">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <meta name="bingbot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <meta name="language" content="Spanish">
        <meta name="revisit-after" content="7 days">
        <meta name="rating" content="general">
        <meta name="distribution" content="global">
        
        {{-- Geo Tags --}}
        <meta name="geo.region" content="MX-JAL">
        <meta name="geo.placename" content="Guadalajara">
        <meta name="geo.position" content="20.6597;-103.3496">
        <meta name="ICBM" content="20.6597, -103.3496">
        
        {{-- Open Graph / Facebook --}}
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://link.cnva.mx{{ request()->getRequestUri() }}">
        <meta property="og:title" content="Link — Tu tarjeta digital inteligente | CNVA - Conectiva IT Solutions">
        <meta property="og:description" content="La forma más fácil, moderna y profesional de compartir toda tu información, enlaces y catálogos desde un solo lugar. Por CNVA - Conectiva IT Solutions en Guadalajara.">
        <meta property="og:image" content="https://link.cnva.mx/favicon/web-app-manifest-512x512.png">
        <meta property="og:image:width" content="512">
        <meta property="og:image:height" content="512">
        <meta property="og:locale" content="es_MX">
        <meta property="og:site_name" content="Link - CNVA">
        
        {{-- Twitter --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="https://link.cnva.mx{{ request()->getRequestUri() }}">
        <meta name="twitter:title" content="Link — Tu tarjeta digital inteligente | CNVA">
        <meta name="twitter:description" content="La forma más fácil, moderna y profesional de compartir toda tu información, enlaces y catálogos desde un solo lugar.">
        <meta name="twitter:image" content="https://link.cnva.mx/favicon/web-app-manifest-512x512.png">
        
        {{-- PWA Meta Tags --}}
        <meta name="application-name" content="Link - CNVA">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Link">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="theme-color" content="#6366f1" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="#6366f1" media="(prefers-color-scheme: dark)">
        <meta name="msapplication-TileColor" content="#6366f1">
        <meta name="msapplication-config" content="/browserconfig.xml">
        
        {{-- Manifest --}}
        <link rel="manifest" href="/favicon/site.webmanifest">
        
        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.06 0.015 270);
            }
        </style>

        <title inertia>Link — Tu tarjeta digital inteligente | CNVA - Conectiva IT Solutions</title>

        {{-- Icons --}}
        <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico">
        <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg">
        <link rel="icon" type="image/png" sizes="96x96" href="/favicon/favicon-96x96.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">

        {{-- Fonts --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800,900|space-grotesk:400,500,600,700" rel="stylesheet" />

        {{-- Canonical URL --}}
        <link rel="canonical" href="https://link.cnva.mx{{ request()->getRequestUri() }}">

        {{-- Structured Data (JSON-LD) --}}
        @php
            $websiteSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'Link - CNVA',
                'alternateName' => 'Link',
                'url' => 'https://link.cnva.mx/',
                'description' => 'La forma más fácil, moderna y profesional de compartir toda tu información, enlaces y catálogos desde un solo lugar. Tu tarjeta digital inteligente por CNVA - Conectiva IT Solutions.',
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'CNVA - Conectiva IT Solutions',
                    'alternateName' => 'Conectiva',
                    'url' => 'https://cnva.mx/',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => 'https://link.cnva.mx/favicon/web-app-manifest-512x512.png'
                    ],
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressLocality' => 'Guadalajara',
                        'addressRegion' => 'Jalisco',
                        'addressCountry' => 'MX'
                    ],
                    'sameAs' => [
                        'https://cnva.mx/'
                    ]
                ],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => 'https://link.cnva.mx/?q={search_term_string}'
                    ],
                    'query-input' => 'required name=search_term_string'
                ],
                'inLanguage' => 'es-MX'
            ];

            $organizationSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => 'CNVA - Conectiva IT Solutions',
                'alternateName' => 'Conectiva',
                'url' => 'https://cnva.mx/',
                'logo' => 'https://link.cnva.mx/favicon/web-app-manifest-512x512.png',
                'description' => 'Marketing Digital en Guadalajara. La tecnología por si sola no cambia nada, una estrategia correcta lo transforma todo.',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Guadalajara',
                    'addressRegion' => 'Jalisco',
                    'postalCode' => '44100',
                    'addressCountry' => 'MX'
                ],
                'geo' => [
                    '@type' => 'GeoCoordinates',
                    'latitude' => '20.6597',
                    'longitude' => '-103.3496'
                ],
                'areaServed' => [
                    '@type' => 'City',
                    'name' => 'Guadalajara'
                ],
                'sameAs' => [
                    'https://cnva.mx/'
                ]
            ];
        @endphp

        <script type="application/ld+json">
        {!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>

        <script type="application/ld+json">
        {!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>

        @vite('resources/js/app.ts')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        
        {{-- Service Worker Registration --}}
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').catch(() => {});
                });
            }
        </script>
    </body>
</html>
