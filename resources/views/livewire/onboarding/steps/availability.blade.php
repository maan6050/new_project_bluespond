<div class="space-y-6">
    @php
        $inputClass = fn (string $key) => 'block w-full rounded-lg border bg-white px-3 py-2.5 text-sm shadow-sm transition-colors focus:outline-none focus:ring-2 disabled:bg-slate-100 disabled:text-slate-400 '
            .($errors->has($key)
                ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                : 'border-slate-300 focus:border-teal-500 focus:ring-teal-500/20');
    @endphp

    <div>
        <h2 class="text-xl font-bold text-slate-900">{{ __('When are you open?') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Set your weekly business hours. Customers can only book during these times.') }}</p>
    </div>

    <div class="space-y-2">
        @foreach ($hours as $index => $h)
            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4" wire:key="hours-{{ $index }}">
                <div class="grid grid-cols-12 items-center gap-3">
                    <div class="col-span-3 text-sm font-semibold text-slate-900">
                        {{ $h['day_name'] }}
                    </div>
                    <div class="col-span-3">
                        <label class="inline-flex cursor-pointer items-center text-sm text-slate-700">
                            <input type="checkbox" wire:model.live="hours.{{ $index }}.is_closed"
                                   class="rounded border-slate-300 text-teal-600 transition-colors focus:ring-2 focus:ring-teal-500/20">
                            <span class="ml-2 font-medium">{{ __('Closed') }}</span>
                        </label>
                    </div>
                    <div class="col-span-3">
                        <input type="time"
                               wire:model.blur="hours.{{ $index }}.open_time"
                               @if ($h['is_closed']) disabled @endif
                               class="{{ $inputClass("hours.{$index}.open_time") }}">
                    </div>
                    <div class="col-span-3">
                        <input type="time"
                               wire:model.blur="hours.{{ $index }}.close_time"
                               @if ($h['is_closed']) disabled @endif
                               class="{{ $inputClass("hours.{$index}.close_time") }}">
                    </div>
                </div>
                @if ($errors->has("hours.{$index}.open_time") || $errors->has("hours.{$index}.close_time"))
                    <div class="mt-2 space-y-1">
                        @error("hours.{$index}.open_time") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        @error("hours.{$index}.close_time") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @error('hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
