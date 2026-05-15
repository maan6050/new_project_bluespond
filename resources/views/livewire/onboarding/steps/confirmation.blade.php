<div class="text-center">
    <div class="mb-5 inline-flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
        <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ __('Your business is live!') }}</h1>
    <p class="mx-auto mt-3 max-w-md text-base text-slate-600">
        {{ __('Customers can now find and book you. Here are a few things you can do next to grow.') }}
    </p>

    @if ($this->publicBookingUrl())
        <div class="mx-auto mt-8 inline-block rounded-xl border border-blue-100 bg-blue-50/60 px-5 py-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">{{ __('Your public booking page') }}</p>
            <a href="{{ $this->publicBookingUrl() }}" target="_blank"
               class="mt-1 block break-all font-mono text-sm font-medium text-blue-900 hover:underline">{{ $this->publicBookingUrl() }}</a>
        </div>
    @endif

    <div class="mx-auto mt-10 grid max-w-2xl grid-cols-1 gap-3 text-left md:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5 transition-shadow hover:shadow-sm">
            <h3 class="font-semibold text-slate-900">{{ __('Add staff members') }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ __('Invite your team so customers can book with their preferred provider.') }}</p>
            <span class="mt-3 inline-block text-xs italic text-slate-400">{{ __('Coming soon') }}</span>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 transition-shadow hover:shadow-sm">
            <h3 class="font-semibold text-slate-900">{{ __('Import customers') }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ __('Upload a CSV of your existing customer list to start running campaigns.') }}</p>
            <span class="mt-3 inline-block text-xs italic text-slate-400">{{ __('Coming soon') }}</span>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 transition-shadow hover:shadow-sm">
            <h3 class="font-semibold text-slate-900">{{ __('Set up your first campaign') }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ __('Reconnect with inactive customers and recover lost revenue automatically.') }}</p>
            <span class="mt-3 inline-block text-xs italic text-slate-400">{{ __('Coming soon') }}</span>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 transition-shadow hover:shadow-sm">
            <h3 class="font-semibold text-slate-900">{{ __('Add social media links') }}</h3>
            <p class="mt-1 text-sm text-slate-600">{{ __('Connect Instagram, Facebook, and more to your public profile.') }}</p>
            <a href="{{ url('/dashboard') }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-blue-700 hover:text-blue-800">
                {{ __('Go to dashboard') }}
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="mt-10">
        <a href="{{ url('/dashboard') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            {{ __('Go to dashboard') }}
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>
