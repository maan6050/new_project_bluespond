@props(['blockers' => [], 'businessSettingsUrl' => '#'])

@php
    $hasBlockers = count($blockers) > 0;
@endphp

<div class="mb-6 px-6 pt-6">
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-gray-900">
        {{-- Blue accent rail on the left (Bluespond info/accent token) --}}
        <div class="absolute inset-y-0 left-0 w-1 bg-blue-600 dark:bg-blue-500"></div>

        <div class="flex flex-col gap-4 p-5 pl-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                {{-- Info icon in soft blue circle --}}
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/40">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                        {{ __('Your business is offline') }}
                    </p>
                    <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-400">
                        @if ($hasBlockers)
                            {{ __('To go live, add:') }}
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ implode(', ', $blockers) }}</span>.
                        @else
                            {{ __('Toggle Publish on in Business Settings to make your booking page visible.') }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 items-center sm:ml-4">
                <a href="{{ $businessSettingsUrl }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-700 dark:hover:bg-blue-600 dark:ring-offset-gray-900">
                    {{ __('Fix & Publish') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
