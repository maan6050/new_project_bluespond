<x-nav.item route="#features">{{ __('Features') }}</x-nav.item>
<x-nav.item route="#tech-stack">{{ __('Tech Stack') }}</x-nav.item>
<x-nav.item route="#pricing">{{ __('Pricing') }}</x-nav.item>
<x-nav.item route="#faq">{{ __('FAQ') }}</x-nav.item>
@guest
    <x-nav.item route="login" class="md:hidden">{{ __('Login') }}</x-nav.item>
@endguest
