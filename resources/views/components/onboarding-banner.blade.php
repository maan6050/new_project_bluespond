@props(['currentStep' => 1, 'totalSteps' => 4])

@php
    $progressPercent = (int) round(($currentStep / $totalSteps) * 100);
@endphp

<div class="mb-6 px-6 pt-6">
    <div class="relative overflow-hidden rounded-xl border border-teal-200 bg-gradient-to-r from-teal-50 to-white shadow-sm dark:border-teal-800 dark:from-teal-950/40 dark:to-gray-900">
        <div class="absolute inset-y-0 left-0 w-1 bg-teal-500"></div>

        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-100 dark:bg-teal-900">
                    <svg class="h-5 w-5 text-teal-600 dark:text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Finish setting up your business') }}
                    </p>
                    <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Step :current of :total · You\'re almost ready to accept bookings.', ['current' => $currentStep, 'total' => $totalSteps]) }}
                    </p>

                    <div class="mt-3 h-1.5 w-full max-w-md overflow-hidden rounded-full bg-teal-100 dark:bg-teal-900">
                        <div class="h-full rounded-full bg-teal-500 transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 items-center sm:ml-4">
                <a href="{{ route('onboarding') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    {{ __('Resume Setup') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
