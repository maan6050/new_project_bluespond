<div class="space-y-6">
    @php
        $inputClass = fn (string $key) => 'block w-full rounded-lg border bg-white px-3 py-2.5 text-sm shadow-sm transition-colors placeholder:text-slate-400 focus:outline-none focus:ring-2 '
            .($errors->has($key)
                ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                : 'border-slate-300 focus:border-teal-500 focus:ring-teal-500/20');
    @endphp

    <div>
        <h2 class="text-xl font-bold text-slate-900">{{ __('What services do you offer?') }}</h2>
        <p class="mt-1 text-sm text-slate-500">
            {{ __("We've pre-filled some common services for your category. Edit, remove, or add to fit your business.") }}
        </p>
    </div>

    @if (count($services) === 0)
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 py-10 text-center">
            <p class="text-sm text-slate-500">{{ __('No services yet. Add your first one below.') }}</p>
        </div>
    @endif

    <div class="space-y-3">
        @foreach ($services as $index => $service)
            <div class="grid grid-cols-12 items-end gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4" wire:key="service-{{ $index }}">
                <div class="col-span-12 sm:col-span-5">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Service name') }}</label>
                    <input type="text" wire:model.blur="services.{{ $index }}.name" maxlength="255"
                           class="{{ $inputClass("services.{$index}.name") }}"
                           placeholder="e.g. Haircut">
                    @error("services.{$index}.name") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-6 sm:col-span-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Duration (min)') }}</label>
                    <input type="number" min="5" max="1440" step="5" wire:model.blur="services.{{ $index }}.duration_minutes"
                           class="{{ $inputClass("services.{$index}.duration_minutes") }}">
                    @error("services.{$index}.duration_minutes") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-5 sm:col-span-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Price (USD)') }}</label>
                    <input type="number" min="0" max="99999.99" step="0.01" wire:model.blur="services.{{ $index }}.price"
                           class="{{ $inputClass("services.{$index}.price") }}">
                    @error("services.{$index}.price") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-1 flex justify-end">
                    <button type="button" wire:click="removeService({{ $index }})"
                            class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                            title="{{ __('Remove') }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" wire:click="addService"
            class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-teal-300 bg-teal-50/40 px-4 py-2.5 text-sm font-semibold text-teal-700 transition-colors hover:border-teal-400 hover:bg-teal-50">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('Add another service') }}
    </button>

    @error('services') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
