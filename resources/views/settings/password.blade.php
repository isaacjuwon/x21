<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => [
                    'required',
                    'string',
                    'current_password',
                ],
                'password' => [
                    'required',
                    'string',
                    Password::defaults(),
                    'confirmed',
                ],
            ]);
        } catch (ValidationException $e) {
            $this->reset(
                'current_password',
                'password',
                'password_confirmation',
            );

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }

    public function render()
    {
        return $this->view()
            ->title('Password Settings')
            ->layout('layouts::app');
    }
};
?>

<section class="w-full mx-4">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form wire:submit="updatePassword" class="my-6 space-y-6">

            <x-ui.field>
                <x-ui.label>{{ __('Current password') }}</x-ui.label>
                <x-ui.input
                    wire:model="current_password"
                    type="password"
                    revealable
                    autocomplete="current-password"
                />
                <x-ui.error name="current_password" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('New password') }}</x-ui.label>
                <x-ui.input
                    wire:model="password"
                    type="password"
                    revealable
                    autocomplete="new-password"
                />
                <x-ui.error name="password" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('Confirm Password') }}</x-ui.label>
                <x-ui.input
                    wire:model="password_confirmation"
                    type="password"
                    revealable
                    autocomplete="new-password"
                />
                <x-ui.error name="password_confirmation" />
            </x-ui.field>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <x-ui.button variant="primary" type="submit" class="w-full" data-test="update-password-button">
                        {{ __('Save') }}
                    </x-ui.button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
