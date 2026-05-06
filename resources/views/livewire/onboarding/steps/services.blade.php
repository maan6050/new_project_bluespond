<div class="space-y-5">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">{{ __('What services do you offer?') }}</h2>
        <p class="text-sm text-gray-500 mt-1">
            {{ __("We've pre-filled some common services for your category. Edit, remove, or add to fit your business.") }}
        </p>
    </div>

    @if (count($services) === 0)
        <div class="text-center py-8 bg-gray-50 rounded-md">
            <p class="text-sm text-gray-500">{{ __('No services yet. Add your first one below.') }}</p>
        </div>
    @endif

    <div class="space-y-3">
        @foreach ($services as $index => $service)
            <div class="grid grid-cols-12 gap-3 items-end p-3 bg-gray-50 rounded-md" wire:key="service-{{ $index }}">
                <div class="col-span-5">
                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Service name') }}</label>
                    <input type="text" wire:model="services.{{ $index }}.name"
                           class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
                           placeholder="e.g. Haircut">
                    @error("services.{$index}.name") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Duration (min)') }}</label>
                    <input type="number" min="5" max="1440" step="5" wire:model="services.{{ $index }}.duration_minutes"
                           class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    @error("services.{$index}.duration_minutes") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Price (USD)') }}</label>
                    <input type="number" min="0" step="0.01" wire:model="services.{{ $index }}.price"
                           class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    @error("services.{$index}.price") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-1">
                    <button type="button" wire:click="removeService({{ $index }})"
                            class="p-2 text-gray-400 hover:text-red-600" title="{{ __('Remove') }}">
                        &times;
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" wire:click="addService"
            class="text-sm text-primary-600 hover:text-primary-700 font-medium">
        + {{ __('Add another service') }}
    </button>

    @error('services') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
