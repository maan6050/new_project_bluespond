@props([
    'height' => 9,
])

{{-- Full Bluespond lockup (icon + wordmark). --}}
{{-- The wordmark uses currentColor — apply Tailwind text-* classes to control it: --}}
{{--   Light theme:  <x-bluespond.logo class="text-slate-900" /> --}}
{{--   Dark theme:   <x-bluespond.logo class="text-white" />     --}}
{{--   Auto theme:   <x-bluespond.logo class="text-slate-900 dark:text-white" /> --}}
<svg
    {{ $attributes->class("h-{$height} w-auto") }}
    viewBox="0 0 220 48"
    xmlns="http://www.w3.org/2000/svg"
    aria-label="{{ __('Bluespond') }}"
    role="img"
    fill="none"
>
    <rect width="48" height="48" rx="10" fill="#1e3a8a"/>
    <path d="M24 14C24 14 16 22 16 30a8 8 0 0 0 16 0C32 22 24 14 24 14Z" fill="#ffffff"/>
    <text
        x="60"
        y="33"
        font-family="Inter, system-ui, -apple-system, Segoe UI, sans-serif"
        font-weight="700"
        font-size="24"
        letter-spacing="-0.5"
        fill="currentColor"
    >Bluespond</text>
</svg>
