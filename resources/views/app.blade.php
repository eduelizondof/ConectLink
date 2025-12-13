<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
        
        {{-- SEO Meta Tags --}}
        <meta name="description" content="ConectLink - La forma más fácil, moderna y profesional de compartir toda tu información, enlaces y catálogos desde un solo lugar. Tu tarjeta digital inteligente por Conectiva ITS.">
        <meta name="keywords" content="tarjeta digital, linktree, link in bio, tarjeta de presentación, QR, contacto digital, ConectLink, Conectiva ITS">
        <meta name="author" content="Conectiva ITS">
        <meta name="robots" content="index, follow">
        <meta name="googlebot" content="index, follow">
        
        {{-- Open Graph / Facebook --}}
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:title" content="ConectLink — Tu tarjeta digital inteligente">
        <meta property="og:description" content="La forma más fácil, moderna y profesional de compartir toda tu información, enlaces y catálogos desde un solo lugar.">
        <meta property="og:image" content="{{ asset('og-image.png') }}">
        <meta property="og:locale" content="es_MX">
        <meta property="og:site_name" content="ConectLink">
        
        {{-- Twitter --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url('/') }}">
        <meta name="twitter:title" content="ConectLink — Tu tarjeta digital inteligente">
        <meta name="twitter:description" content="La forma más fácil, moderna y profesional de compartir toda tu información, enlaces y catálogos desde un solo lugar.">
        <meta name="twitter:image" content="{{ asset('og-image.png') }}">
        
        {{-- PWA Meta Tags --}}
        <meta name="application-name" content="ConectLink">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="ConectLink">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="theme-color" content="#06b6d4" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="#0891b2" media="(prefers-color-scheme: dark)">
        <meta name="msapplication-TileColor" content="#06b6d4">
        <meta name="msapplication-config" content="/browserconfig.xml">
        
        {{-- Manifest --}}
        <link rel="manifest" href="/manifest.json">
        
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

        <title inertia>{{ config('app.name', 'ConectLink') }}</title>

        {{-- Icons --}}
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

        {{-- Fonts --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800,900|space-grotesk:400,500,600,700" rel="stylesheet" />

        {{-- Canonical URL --}}
        <link rel="canonical" href="{{ url()->current() }}">

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
