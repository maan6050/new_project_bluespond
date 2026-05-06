<div class="space-y-5">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">{{ __('When are you open?') }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ __('Set your weekly business hours. Customers can only book during these times.') }}</p>
    </div>

    <div class="space-y-2">
        @foreach ($hours as $index => $h)
            <div class="grid grid-cols-12 gap-3 items-center p-3 bg-gray-50 rounded-md" wire:key="hours-{{ $index }}">
                <div class="col-span-3 font-medium text-gray-900">
                    {{ $h['day_name'] }}
                </div>
                <div class="col-span-3">
                    <label class="inline-flex items-center text-sm text-gray-700">
                        <input type="checkbox" wire:model.live="hours.{{ $index }}.is_closed"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2">{{ __('Closed') }}</span>
                    </label>
                </div>
                <div class="col-span-3">
                    <input type="time"
                           wire:model="hours.{{ $index }}.open_time"
                           @if ($h['is_closed']) disabled @endif
                           class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm disabled:bg-gray-100 disabled:text-gray-400">
                </div>
                <div class="col-span-3">
                    <input type="time"
                           wire:model="hours.{{ $index }}.close_time"
                           @if ($h['is_closed']) disabled @endif
                           class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm disabled:bg-gray-100 disabled:text-gray-400">
                </div>
            </div>
        @endforeach
    </div>

    @error('hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
