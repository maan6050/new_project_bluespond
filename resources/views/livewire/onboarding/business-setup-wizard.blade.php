<div>
    @if ($completed)
        @include('livewire.onboarding.steps.confirmation')
    @else
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('Set up your business') }}</h1>
                <span class="text-sm text-gray-500">{{ __('Step :current of :total', ['current' => $currentStep, 'total' => $totalSteps]) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-primary-600 h-2 rounded-full transition-all" style="width: {{ $progressPercent }}%"></div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-500">
                <span class="{{ $currentStep >= 1 ? 'font-semibold text-primary-700' : '' }}">1. {{ __('Business Info') }}</span>
                <span class="{{ $currentStep >= 2 ? 'font-semibold text-primary-700' : '' }}">2. {{ __('Services') }}</span>
                <span class="{{ $currentStep >= 3 ? 'font-semibold text-primary-700' : '' }}">3. {{ __('Hours') }}</span>
                <span class="{{ $currentStep >= 4 ? 'font-semibold text-primary-700' : '' }}">4. {{ __('Publish') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @if ($currentStep === 1)
                @include('livewire.onboarding.steps.business-info', ['categories' => $categories])
            @elseif ($currentStep === 2)
                @include('livewire.onboarding.steps.services')
            @elseif ($currentStep === 3)
                @include('livewire.onboarding.steps.availability')
            @elseif ($currentStep === 4)
                @include('livewire.onboarding.steps.publish')
            @endif

            <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200">
                <button type="button"
                        wire:click="prevStep"
                        @if ($currentStep <= 1) disabled @endif
                        class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900 disabled:opacity-30 disabled:cursor-not-allowed">
                    &larr; {{ __('Back') }}
                </button>
                <button type="button"
                        wire:click="nextStep"
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md text-sm font-medium disabled:opacity-50">
                    <span wire:loading.remove>
                        {{ $currentStep < $totalSteps ? __('Continue') : __('Finish & Publish') }}
                    </span>
                    <span wire:loading>{{ __('Saving...') }}</span>
                </button>
            </div>
        </div>
    @endif
</div>
