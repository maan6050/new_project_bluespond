<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">{{ __('Almost done — make it public') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Add a logo and a cover image to brand your public profile.') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Logo') }}</label>
            <input type="file" wire:model="logo" accept="image/*"
                   class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
            <p class="mt-1.5 text-xs text-slate-500">{{ __('JPG, PNG, WebP, or SVG. Max 4MB.') }}</p>
            @error('logo') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            <div wire:loading wire:target="logo" class="mt-2 text-xs text-slate-500">{{ __('Uploading...') }}</div>
            @if ($logo)
                <div wire:loading.remove wire:target="logo" class="mt-3">
                    <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview"
                         class="h-24 w-24 rounded-xl border border-slate-200 bg-white object-cover shadow-sm">
                    <p class="mt-1.5 truncate text-xs text-slate-500">{{ $logo->getClientOriginalName() }}</p>
                </div>
            @endif
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Cover image') }}</label>
            <input type="file" wire:model="coverImage" accept="image/*"
                   class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
            <p class="mt-1.5 text-xs text-slate-500">{{ __('JPG, PNG, or WebP. Max 8MB.') }}</p>
            @error('coverImage') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            <div wire:loading wire:target="coverImage" class="mt-2 text-xs text-slate-500">{{ __('Uploading...') }}</div>
            @if ($coverImage)
                <div wire:loading.remove wire:target="coverImage" class="mt-3">
                    <img src="{{ $coverImage->temporaryUrl() }}" alt="Cover preview"
                         class="h-24 w-full rounded-xl border border-slate-200 bg-white object-cover shadow-sm">
                    <p class="mt-1.5 truncate text-xs text-slate-500">{{ $coverImage->getClientOriginalName() }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-4">
        <p class="text-sm font-semibold text-blue-900">{{ __('Your public booking URL') }}</p>
        <p class="mt-1 break-all font-mono text-sm text-blue-700">
            @if ($this->publicBookingUrl())
                {{ $this->publicBookingUrl() }}
            @else
                <span class="italic text-blue-600/70">{{ __('Will be generated after step 1') }}</span>
            @endif
        </p>
    </div>

    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 transition-colors hover:border-blue-300 hover:bg-blue-50/40">
        <input type="checkbox" wire:model="isPublished"
               class="mt-1 rounded border-slate-300 text-blue-600 transition-colors focus:ring-2 focus:ring-blue-500/20">
        <div>
            <span class="block text-sm font-semibold text-slate-900">{{ __('Publish my business') }}</span>
            <span class="mt-0.5 block text-xs text-slate-500">
                {{ __('Customers can find and book you when this is on. You can toggle it off anytime from your dashboard.') }}
            </span>
        </div>
    </label>
</div>
