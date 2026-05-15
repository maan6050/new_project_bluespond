<x-layouts.focus>
    <x-slot name="left">
        <div class="flex flex-col py-2 md:p-10 gap-4 justify-center h-full items-center">
            <div class="card w-full md:max-w-xl bg-base-100 shadow-xl p-4 md:p-8">

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <x-input.field label="{{ __('Email Address') }}" type="email" name="email"
                                   value="{{ $email ?? old('email') }}" required autofocus="true" class="my-2"
                                   autocomplete="email" max-width="w-full"/>

                    @error('email')
                        <span class="text-xs text-red-500" role="alert">
                            {{ $message }}
                        </span>
                    @enderror

                    <x-input.field label="{{ __('Password') }}" type="password" name="password" required class="my-2"  max-width="w-full"/>

                    @error('password')
                        <span class="text-xs text-red-500" role="alert">
                            {{ $message }}
                        </span>
                    @enderror

                    <x-input.field label="{{ __('Confirm Password') }}" type="password" name="password_confirmation" required class="my-2"  max-width="w-full"/>

                    @error('password')
                    <span class="text-xs text-red-500" role="alert">
                            {{ $message }}
                        </span>
                    @enderror

                    <button type="submit"
                            class="my-2 inline-flex w-full items-center justify-center rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('Reset Password') }}
                    </button>

                </form>
            </div>
        </div>
    </x-slot>


    <x-slot name="right">
        <div class="py-4 md:px-12 md:pt-36 h-full">
            <x-heading.h1 class="text-3xl! md:text-4xl! font-semibold!">
                {{ __('Reset Your Password.') }}
            </x-heading.h1>
            <p class="mt-4">
                {{ __('You are 1 step away from resetting your password.') }}
            </p>
        </div>
    </x-slot>

</x-layouts.focus>
