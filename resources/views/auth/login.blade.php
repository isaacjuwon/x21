<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <x-ui.field>
                <x-ui.label for="email">{{ __('Email address') }}</x-ui.label>
                <x-ui.input 
                    name="email" 
                    id="email"
                    type="email" 
                    required 
                    autofocus 
                    autocomplete="email" 
                    placeholder="email@example.com"
                />
                <x-ui.error name="email" />
            </x-ui.field>

            <!-- Password -->
            <div class="relative">
                <x-ui.field>
                    <x-ui.label for="password">{{ __('Password') }}</x-ui.label>
                    <x-ui.input 
                        name="password" 
                        id="password"
                        type="password" 
                        required 
                        autocomplete="current-password" 
                        placeholder="{{ __('Password') }}"
                        viewable
                    />
                    <x-ui.error name="password" />
                </x-ui.field>

                @if (Route::has('password.request'))
                    <x-ui.link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </x-ui.link>
                @endif
            </div>

            <!-- Remember Me -->
            <x-ui.checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <x-ui.button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </x-ui.button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <x-ui.link :href="route('register')" wire:navigate>{{ __('Sign up') }}</x-ui.link>
            </div>
        @endif
    </div>
</x-layouts::auth>
