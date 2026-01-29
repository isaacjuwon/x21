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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.field>
                    <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('Current Password') }}</x-ui.label>
                    <x-ui.input
                        wire:model="current_password"
                        type="password"
                        revealable
                        autocomplete="current-password"
                        class="text-base font-bold tracking-widest h-14"
                    />
                    <x-ui.error name="current_password" />
                </x-ui.field>

                <div class="hidden md:block"></div>

                <x-ui.field>
                    <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('New Password') }}</x-ui.label>
                    <x-ui.input
                        wire:model="password"
                        type="password"
                        revealable
                        autocomplete="new-password"
                        class="text-base font-bold tracking-widest h-14"
                    />
                    <x-ui.error name="password" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('Confirm Password') }}</x-ui.label>
                    <x-ui.input
                        wire:model="password_confirmation"
                        type="password"
                        revealable
                        autocomplete="new-password"
                        class="text-base font-bold tracking-widest h-14"
                    />
                    <x-ui.error name="password_confirmation" />
                </x-ui.field>
            </div>

            <div class="flex items-center gap-6 pt-4">
                <x-ui.button variant="primary" type="submit" class="h-14 px-12 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20" data-test="update-password-button">
                    {{ __('Update Password') }}
                </x-ui.button>

                <x-action-message class="text-xs font-bold text-success uppercase tracking-widest" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
