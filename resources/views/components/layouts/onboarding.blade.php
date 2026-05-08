<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Set up your business') }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2C12 2 5 9.5 5 15a7 7 0 0 0 14 0c0-5.5-7-13-7-13z"/>
                    </svg>
                </span>
                <div class="flex flex-col leading-tight">
                    <span class="text-base font-bold tracking-tight text-slate-900">{{ config('app.name') }}</span>
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ __('Business setup') }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                    {{ __('Skip for now') }}
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="text-sm text-slate-500 transition-colors hover:text-slate-900">
                    {{ __('Sign out') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-6 py-10 sm:py-16">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
