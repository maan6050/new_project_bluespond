<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">{{ __('When are you open?') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Set your weekly business hours. Customers can only book during these times.') }}</p>
    </div>

    <div class="space-y-2">
        @foreach ($hours as $index => $h)
            <div class="grid grid-cols-12 items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4" wire:key="hours-{{ $index }}">
                <div class="col-span-3 text-sm font-semibold text-slate-900">
                    {{ $h['day_name'] }}
                </div>
                <div class="col-span-3">
                    <label class="inline-flex cursor-pointer items-center text-sm text-slate-700">
                        <input type="checkbox" wire:model.live="hours.{{ $index }}.is_closed"
                               class="rounded border-slate-300 text-blue-600 transition-colors focus:ring-2 focus:ring-blue-500/20">
                        <span class="ml-2 font-medium">{{ __('Closed') }}</span>
                    </label>
                </div>
                <div class="col-span-3">
                    <input type="time"
                           wire:model="hours.{{ $index }}.open_time"
                           @if ($h['is_closed']) disabled @endif
                           class="block w-full rounded-lg border-slate-300 bg-white shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm disabled:bg-slate-100 disabled:text-slate-400">
                </div>
                <div class="col-span-3">
                    <input type="time"
                           wire:model="hours.{{ $index }}.close_time"
                           @if ($h['is_closed']) disabled @endif
                           class="block w-full rounded-lg border-slate-300 bg-white shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm disabled:bg-slate-100 disabled:text-slate-400">
                </div>
            </div>
        @endforeach
    </div>

    @error('hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
