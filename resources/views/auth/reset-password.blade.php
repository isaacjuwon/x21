<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <x-ui.field>
                <x-ui.label for="email">{{ __('Email') }}</x-ui.label>
                <x-ui.input 
                    name="email" 
                    id="email"
                    value="{{ request('email') }}"
                    type="email" 
                    required 
                    autocomplete="email"
                />
                <x-ui.error name="email" />
            </x-ui.field>

            <!-- Password -->
            <x-ui.field>
                <x-ui.label for="password">{{ __('Password') }}</x-ui.label>
                <x-ui.input 
                    name="password" 
                    id="password"
                    type="password" 
                    required 
                    autocomplete="new-password" 
                    placeholder="{{ __('Password') }}"
                    viewable
                />
                <x-ui.error name="password" />
            </x-ui.field>

            <!-- Confirm Password -->
            <x-ui.field>
                <x-ui.label for="password_confirmation">{{ __('Confirm password') }}</x-ui.label>
                <x-ui.input 
                    name="password_confirmation" 
                    id="password_confirmation"
                    type="password" 
                    required 
                    autocomplete="new-password" 
                    placeholder="{{ __('Confirm password') }}"
                    viewable
                />
                <x-ui.error name="password_confirmation" />
            </x-ui.field>

            <div class="flex items-center justify-end">
                <x-ui.button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    {{ __('Reset password') }}
                </x-ui.button>
            </div>
        </form>
    </div>
</x-layouts::auth>
