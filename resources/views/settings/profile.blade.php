<?php

use App\Enums\Media\MediaCollectionType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $phone_number = '';

    #[Rule('nullable|image|max:1024')] // 1MB Max
    public $avatar;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->phone_number = Auth::user()->phone_number ?? '';
        $this->avatar = null;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'phone_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($this->avatar) {
            $user->addMedia($this->avatar)
                ->toMediaCollection(MediaCollectionType::Avatar->value);
        }

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(
                default: route('dashboard', absolute: false),
            );

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function render()
    {
        return $this->view()
            ->title('Profile Settings')
            ->layout('layouts::app');
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="flex items-center gap-6">
                <div class="relative">
                    @if(!is_null($avatar))
                        <img src="{{ $avatar->temporaryUrl() }}" class="h-20 w-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700">
                    @elseif(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" class="h-20 w-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700">
                    @else
                        <div class="h-20 w-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 border-2 border-gray-200 dark:border-gray-700">
                            <span class="text-2xl font-bold">{{ auth()->user()->initials() }}</span>
                        </div>
                    @endif
                    
                    <label for="avatar-upload" class="absolute bottom-0 right-0 p-1 bg-white dark:bg-gray-800 rounded-full shadow-sm border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <x-ui.icon name="camera" class="w-4 h-4 text-gray-500" />
                        <input type="file" wire:model="avatar" id="avatar-upload" class="hidden" accept="image/*">
                    </label>
                </div>
                
                <div class="flex-1">
                    <x-ui.field>
                        <x-ui.label>{{ __('Name') }}</x-ui.label>
                        <x-ui.input wire:model="name" type="text" required autofocus autocomplete="name" />
                        <x-ui.error name="name" />
                    </x-ui.field>
                </div>
            </div>

            <x-ui.error name="avatar" />

            <div>
                <x-ui.field>
                    <x-ui.label>{{ __('Email') }}</x-ui.label>
                    <x-ui.input wire:model="email" type="email" required autocomplete="email" />
                    <x-ui.error name="email" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>{{ __('Phone Number') }}</x-ui.label>
                    <x-ui.input wire:model="phone_number" type="tel" />
                    <x-ui.error name="phone_number" />
                </x-ui.field>

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <x-ui.text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <x-ui.link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </x-ui.link>
                        </x-ui.text>

                        @if (session('status') === 'verification-link-sent')
                            <x-ui.text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </x-ui.text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <x-ui.button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </x-ui.button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings::delete-user-form />
    </x-settings.layout>
</section>
