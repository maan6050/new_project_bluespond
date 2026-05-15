<div>
    @if ($completed)
        @include('livewire.onboarding.steps.confirmation')
    @else
        @php
            $steps = [
                1 => __('Business Info'),
                2 => __('Services'),
                3 => __('Hours'),
                4 => __('Publish'),
            ];
        @endphp

        <div class="mb-8">
            <div class="mb-6 flex items-end justify-between">
                <div>
                    <p class="text-sm font-medium text-teal-600">{{ __('Step :current of :total', ['current' => $currentStep, 'total' => $totalSteps]) }}</p>
                    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ __('Set up your business') }}</h1>
                </div>
                <span class="hidden text-sm font-semibold text-slate-500 sm:inline">
                    {{ $progressPercent }}%
                </span>
            </div>

            <ol class="grid grid-cols-4 gap-2 sm:gap-3">
                @foreach ($steps as $stepNumber => $stepLabel)
                    @php
                        $isComplete = $stepNumber < $currentStep;
                        $isActive = $stepNumber === $currentStep;
                        $isUpcoming = $stepNumber > $currentStep;
                    @endphp
                    <li class="flex flex-col gap-2">
                        <span @class([
                            'block h-1.5 w-full rounded-full transition-colors duration-300',
                            'bg-teal-500' => $isComplete || $isActive,
                            'bg-slate-200' => $isUpcoming,
                        ])></span>
                        <span @class([
                            'flex items-center gap-1.5 text-xs font-medium tracking-wide',
                            'text-teal-600' => $isComplete,
                            'text-slate-900' => $isActive,
                            'text-slate-400' => $isUpcoming,
                        ])>
                            @if ($isComplete)
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <span @class([
                                    'flex h-4 w-4 items-center justify-center rounded-full text-[10px] font-bold',
                                    'bg-teal-500 text-white' => $isActive,
                                    'bg-slate-200 text-slate-500' => $isUpcoming,
                                ])>{{ $stepNumber }}</span>
                            @endif
                            <span class="hidden sm:inline">{{ $stepLabel }}</span>
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="p-6 sm:p-8">
                @if ($currentStep === 1)
                    @include('livewire.onboarding.steps.business-info', ['categories' => $categories])
                @elseif ($currentStep === 2)
                    @include('livewire.onboarding.steps.services')
                @elseif ($currentStep === 3)
                    @include('livewire.onboarding.steps.availability')
                @elseif ($currentStep === 4)
                    @include('livewire.onboarding.steps.publish')
                @endif
            </div>

            <div class="flex items-center justify-between rounded-b-2xl border-t border-slate-200 bg-slate-50 px-6 py-4 sm:px-8">
                <button type="button"
                        wire:click="prevStep"
                        @if ($currentStep <= 1) disabled @endif
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-white hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('Back') }}
                </button>

                <button type="button"
                        wire:click="nextStep"
                        wire:loading.attr="disabled"
                        class="relative inline-flex min-w-45 items-center justify-center gap-1.5 rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-800 hover:shadow focus:outline-none focus:ring-2 focus:ring-blue-900/40 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-80 disabled:hover:bg-blue-900">
                    <span wire:loading.remove wire:target="nextStep" class="inline-flex items-center gap-1.5 whitespace-nowrap">
                        {{ $currentStep < $totalSteps ? __('Continue') : __('Finish & Publish') }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                    <span wire:loading.inline-flex wire:target="nextStep" class="items-center gap-2 whitespace-nowrap">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Saving...') }}
                    </span>
                </button>
            </div>
        </div>

        <p class="mt-6 text-center text-sm text-slate-500">
            {{ __('Not ready right now?') }}
            <a href="{{ route('dashboard') }}" class="font-medium text-teal-600 transition-colors hover:text-teal-700">
                {{ __('Skip and finish later') }}
            </a>
        </p>
    @endif
</div>
