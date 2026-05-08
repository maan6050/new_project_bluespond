<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Set up your business') }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-xl font-bold text-primary-700">{{ config('app.name') }}</span>
                <span class="text-sm text-gray-500">{{ __('Business setup') }}</span>
            </div>
            <div class="flex items-center gap-5">
                <a href="{{ route('dashboard') }}"
                   class="text-sm font-medium text-gray-600 hover:text-gray-900 hidden sm:inline">
                    {{ __('Skip for now') }} &rarr;
                </a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="text-sm text-gray-500 hover:text-gray-700">
                    {{ __('Sign out') }}
                </a>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </header>
    <main class="max-w-4xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
