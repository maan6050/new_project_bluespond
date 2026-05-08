<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">{{ __('Tell us about your business') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('This is what your customers will see when they find you.') }}</p>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Business name') }} <span class="text-blue-600">*</span></label>
        <input type="text" wire:model="businessName"
               class="block w-full rounded-lg border-slate-300 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
               placeholder="e.g. Bella's Hair Studio">
        @error('businessName') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Category') }} <span class="text-blue-600">*</span></label>
        <select wire:model.live="categoryId"
                wire:change="loadServiceTemplatesForCategory"
                class="block w-full rounded-lg border-slate-300 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm">
            <option value="">{{ __('Select a category') }}</option>
            @foreach ($categories->groupBy('vertical') as $vertical => $items)
                <optgroup label="{{ ucfirst(str_replace('_', ' ', $vertical)) }}">
                    @foreach ($items as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('categoryId') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
        <textarea wire:model="description" rows="3"
                  class="block w-full rounded-lg border-slate-300 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                  placeholder="What makes your business special?"></textarea>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Phone') }}</label>
            <input type="tel" wire:model="phone"
                   class="block w-full rounded-lg border-slate-300 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                   placeholder="+1 555 123 4567">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Timezone') }} <span class="text-blue-600">*</span></label>
            <select wire:model="timezone"
                    class="block w-full rounded-lg border-slate-300 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm">
                @foreach (['America/New_York','America/Chicago','America/Denver','America/Los_Angeles','America/Phoenix','America/Anchorage','Pacific/Honolulu','Europe/London','Europe/Paris','Asia/Dubai','Asia/Kolkata','Asia/Singapore','Australia/Sydney','UTC'] as $tz)
                    <option value="{{ $tz }}">{{ $tz }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Address') }}</label>
        <input type="text" wire:model="addressLine1"
               class="block w-full rounded-lg border-slate-300 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
               placeholder="Street address">
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('City') }}</label>
            <input type="text" wire:model="city"
                   class="block w-full rounded-lg border-slate-300 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('State') }}</label>
            <input type="text" wire:model="state"
                   class="block w-full rounded-lg border-slate-300 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('ZIP') }}</label>
            <input type="text" wire:model="zipCode"
                   class="block w-full rounded-lg border-slate-300 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm">
        </div>
    </div>
</div>
