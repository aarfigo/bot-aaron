<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Fonts (system / Figtree) -->
            <!-- Use system font stack for faster load (Figtree fallback) -->
            <style>
                body, input, button, textarea, select { font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif !important; }
            </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="mb-6">
                {{-- Prefer the provided 'logo daniel.png' image, fall back to auth-logo.svg, then app name --}}
                @if(file_exists(public_path('img/logo daniel.png')))
                    <img src="{{ asset('img/logo daniel.png') }}" class="w-48 h-48 object-contain mx-auto" alt="{{ config('app.name', 'Laravel') }}" />
                @elseif(file_exists(public_path('img/auth-logo.svg')))
                    <img src="{{ asset('img/auth-logo.svg') }}" class="w-48 h-48 object-contain mx-auto" alt="{{ config('app.name', 'Laravel') }}" />
                @else
                    <div class="fw-bold">{{ config('app.name', 'Laravel') }}</div>
                @endif
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
