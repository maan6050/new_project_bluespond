<x-layouts.focus-center>

    <x-slot name="title">
        {{ __('Already Subscribed') }}
    </x-slot>

    <div class="mx-4">
        <div class="card max-w-3xl bg-base-100 shadow-xl mx-auto text-center">
            <div class="card-body">
                @svg('success', 'w-24 h-24 mx-auto text-blue-600 dark:text-blue-400 stroke-blue-600 dark:stroke-blue-400')

                <x-heading.h3 class="text-slate-900 dark:text-slate-100">
                    {{ __("You're already subscribed.") }}
                </x-heading.h3>

                <p class="text-slate-600 dark:text-slate-300">
                    {{ __('Your Bluespond account already has an active plan, so a second one is not needed. Head to your dashboard to continue managing your business.') }}
                </p>

                <x-button-link.primary href="{{ route('dashboard') }}" class="mt-4 mx-auto">
                    {{ __('Go to Dashboard') }}
                </x-button-link.primary>
            </div>
        </div>
    </div>

</x-layouts.focus-center>
