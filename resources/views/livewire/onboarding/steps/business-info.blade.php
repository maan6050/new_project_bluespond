<div class="space-y-6">
    @php
        // Avoid Tailwind class-conflict warnings: emit error styles XOR default
        // styles, never both. Each input applies $inputClass($errorKey).
        $inputClass = fn (string $key) => 'block w-full rounded-lg border bg-white px-3 py-2.5 text-sm shadow-sm transition-colors placeholder:text-slate-400 focus:outline-none focus:ring-2 '
            .($errors->has($key)
                ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                : 'border-slate-300 focus:border-teal-500 focus:ring-teal-500/20');
    @endphp

    <div>
        <h2 class="text-xl font-bold text-slate-900">{{ __('Tell us about your business') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('This is what your customers will see when they find you.') }}</p>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Business name') }} <span class="text-teal-600">*</span></label>
        <input type="text" wire:model.blur="businessName" maxlength="255"
               class="{{ $inputClass('businessName') }}"
               placeholder="e.g. Bella's Hair Studio">
        @error('businessName') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Category') }} <span class="text-teal-600">*</span></label>
        <select wire:model.live="categoryId"
                wire:change="loadServiceTemplatesForCategory"
                class="{{ $inputClass('categoryId') }}">
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
        <textarea wire:model.blur="description" rows="3" maxlength="2000"
                  class="{{ $inputClass('description') }}"
                  placeholder="What makes your business special?"></textarea>
        @error('description') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Phone') }}</label>
            <input type="tel" wire:model.blur="phone" maxlength="20"
                   class="{{ $inputClass('phone') }}"
                   placeholder="+1 555 123 4567">
            @error('phone') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Timezone') }} <span class="text-teal-600">*</span></label>
            <select wire:model.blur="timezone"
                    class="{{ $inputClass('timezone') }}">
                @foreach (['America/New_York','America/Chicago','America/Denver','America/Los_Angeles','America/Phoenix','America/Anchorage','Pacific/Honolulu','Europe/London','Europe/Paris','Asia/Dubai','Asia/Kolkata','Asia/Singapore','Australia/Sydney','UTC'] as $tz)
                    <option value="{{ $tz }}">{{ $tz }}</option>
                @endforeach
            </select>
            @error('timezone') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Address') }}</label>
        <input type="text" wire:model.blur="addressLine1" maxlength="255"
               class="{{ $inputClass('addressLine1') }}"
               placeholder="Street address">
        @error('addressLine1') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('City') }}</label>
            <input type="text" wire:model.blur="city" maxlength="100"
                   class="{{ $inputClass('city') }}">
            @error('city') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('State') }}</label>
            <input type="text" wire:model.blur="state" maxlength="100"
                   class="{{ $inputClass('state') }}">
            @error('state') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('ZIP') }}</label>
            <input type="text" wire:model.blur="zipCode" maxlength="20"
                   class="{{ $inputClass('zipCode') }}">
            @error('zipCode') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
