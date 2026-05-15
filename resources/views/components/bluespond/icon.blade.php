@props([
    'size' => 9,
])

{{-- Bluespond icon — fixed brand colors (deep navy box + white droplet). --}}
{{-- Works on any background regardless of theme since the box is self-contained. --}}
<svg
    {{ $attributes->class("h-{$size} w-{$size}") }}
    viewBox="0 0 48 48"
    xmlns="http://www.w3.org/2000/svg"
    aria-label="{{ __('Bluespond') }}"
    role="img"
    fill="none"
>
    <rect width="48" height="48" rx="10" fill="#1e3a8a"/>
    <path d="M24 14C24 14 16 22 16 30a8 8 0 0 0 16 0C32 22 24 14 24 14Z" fill="#ffffff"/>
</svg>
