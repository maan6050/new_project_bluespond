<div class="text-center py-8">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
    </div>
    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Your business is live!') }}</h1>
    <p class="text-gray-600 mb-6 max-w-md mx-auto">
        {{ __('Customers can now find and book you. Here are a few things you can do next to grow.') }}
    </p>

    @if ($this->publicBookingUrl())
        <div class="inline-block bg-blue-50 border border-blue-200 rounded-md p-3 mb-8">
            <p class="text-xs text-blue-700 mb-1">{{ __('Your public booking page') }}</p>
            <a href="{{ $this->publicBookingUrl() }}" target="_blank"
               class="text-blue-900 font-mono font-medium hover:underline">{{ $this->publicBookingUrl() }}</a>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto text-left">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 mb-1">{{ __('Add staff members') }}</h3>
            <p class="text-sm text-gray-600 mb-3">{{ __('Invite your team so customers can book with their preferred provider.') }}</p>
            <span class="inline-block text-xs text-gray-400 italic">{{ __('Coming soon') }}</span>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 mb-1">{{ __('Import customers') }}</h3>
            <p class="text-sm text-gray-600 mb-3">{{ __('Upload a CSV of your existing customer list to start running campaigns.') }}</p>
            <span class="inline-block text-xs text-gray-400 italic">{{ __('Coming soon') }}</span>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 mb-1">{{ __('Set up your first campaign') }}</h3>
            <p class="text-sm text-gray-600 mb-3">{{ __('Reconnect with inactive customers and recover lost revenue automatically.') }}</p>
            <span class="inline-block text-xs text-gray-400 italic">{{ __('Coming soon') }}</span>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 mb-1">{{ __('Add social media links') }}</h3>
            <p class="text-sm text-gray-600 mb-3">{{ __('Connect Instagram, Facebook, and more to your public profile.') }}</p>
            <a href="{{ url('/dashboard') }}" class="inline-block text-sm text-primary-600 hover:text-primary-700 font-medium">{{ __('Go to dashboard') }} &rarr;</a>
        </div>
    </div>

    <div class="mt-8">
        <a href="{{ url('/dashboard') }}"
           class="inline-block px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md text-sm font-medium">
            {{ __('Go to dashboard') }}
        </a>
    </div>
</div>
