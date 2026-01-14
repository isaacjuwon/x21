<x-layouts::auth>
        <x-auth-header
            :title="__('Confirm password')"
            :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

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

            <x-ui.button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('Confirm') }}
            </x-ui.button>
        </form>
</x-layouts::auth>
