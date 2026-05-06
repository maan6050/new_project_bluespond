<div class="space-y-5">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">{{ __('Almost done — make it public') }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ __('Add a logo and a cover image to brand your public profile.') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Logo') }}</label>
            <input type="file" wire:model="logo" accept="image/*"
                   class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            <p class="mt-1 text-xs text-gray-500">{{ __('JPG, PNG, WebP, or SVG. Max 4MB.') }}</p>
            @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <div wire:loading wire:target="logo" class="mt-2 text-xs text-gray-500">{{ __('Uploading...') }}</div>
            @if ($logo)
                <div wire:loading.remove wire:target="logo" class="mt-2">
                    <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview"
                         class="h-24 w-24 object-cover rounded-md border border-gray-200 bg-white">
                    <p class="mt-1 text-xs text-gray-500 truncate">{{ $logo->getClientOriginalName() }}</p>
                </div>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Cover image') }}</label>
            <input type="file" wire:model="coverImage" accept="image/*"
                   class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            <p class="mt-1 text-xs text-gray-500">{{ __('JPG, PNG, or WebP. Max 8MB.') }}</p>
            @error('coverImage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <div wire:loading wire:target="coverImage" class="mt-2 text-xs text-gray-500">{{ __('Uploading...') }}</div>
            @if ($coverImage)
                <div wire:loading.remove wire:target="coverImage" class="mt-2">
                    <img src="{{ $coverImage->temporaryUrl() }}" alt="Cover preview"
                         class="h-24 w-full object-cover rounded-md border border-gray-200 bg-white">
                    <p class="mt-1 text-xs text-gray-500 truncate">{{ $coverImage->getClientOriginalName() }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
        <p class="text-sm font-medium text-blue-900">{{ __('Your public booking URL') }}</p>
        <p class="text-sm text-blue-700 mt-1 font-mono break-all">
            @if ($this->publicBookingUrl())
                {{ $this->publicBookingUrl() }}
            @else
                <span class="italic">{{ __('Will be generated after step 1') }}</span>
            @endif
        </p>
    </div>

    <label class="flex items-start gap-3 cursor-pointer">
        <input type="checkbox" wire:model="isPublished"
               class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
        <div>
            <span class="block text-sm font-medium text-gray-900">{{ __('Publish my business') }}</span>
            <span class="block text-xs text-gray-500 mt-0.5">
                {{ __('Customers can find and book you when this is on. You can toggle it off anytime from your dashboard.') }}
            </span>
        </div>
    </label>
</div>
