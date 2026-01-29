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
            <div class="flex items-center gap-8 mb-8">
                <div class="relative">
                    @if(!is_null($avatar))
                        <img src="{{ $avatar->temporaryUrl() }}" class="h-24 w-24 rounded-full object-cover border-4 border-neutral-100 dark:border-neutral-700 shadow-sm">
                    @elseif(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" class="h-24 w-24 rounded-full object-cover border-4 border-neutral-100 dark:border-neutral-700 shadow-sm">
                    @else
                        <div class="h-24 w-24 rounded-full bg-neutral-50 dark:bg-neutral-900 flex items-center justify-center text-neutral-400 border-4 border-neutral-100 dark:border-neutral-700 shadow-sm">
                            <span class="text-3xl font-bold">{{ auth()->user()->initials() }}</span>
                        </div>
                    @endif
                    
                    <label for="avatar-upload" class="absolute bottom-1 right-1 p-2 bg-white dark:bg-neutral-800 rounded-full shadow-lg border border-neutral-100 dark:border-neutral-700 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-all hover:scale-110 active:scale-95">
                        <x-ui.icon name="camera" class="w-4 h-4 text-neutral-500" />
                        <input type="file" wire:model="avatar" id="avatar-upload" class="hidden" accept="image/*">
                    </label>
                </div>
                
                <div class="flex-1">
                    <x-ui.field>
                        <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('Full Name') }}</x-ui.label>
                        <x-ui.input wire:model="name" type="text" required autofocus autocomplete="name" class="text-base font-bold tracking-widest h-14" />
                        <x-ui.error name="name" />
                    </x-ui.field>
                </div>
            </div>

            <x-ui.error name="avatar" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.field>
                    <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('Email Address') }}</x-ui.label>
                    <x-ui.input wire:model="email" type="email" required autocomplete="email" class="text-base font-bold tracking-widest h-14" />
                    <x-ui.error name="email" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">{{ __('Phone Number') }}</x-ui.label>
                    <x-ui.input wire:model="phone_number" type="tel" class="text-base font-bold tracking-widest h-14" />
                    <x-ui.error name="phone_number" />
                </x-ui.field>
            </div>

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

            {{-- Referral Link Section --}}
            <div class="bg-neutral-50 dark:bg-neutral-900/50 rounded-[--radius-box] p-6 border border-neutral-100 dark:border-neutral-700 space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white">{{ __('Referral Program') }}</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ __('Share your referral link with friends and earn rewards when they join.') }}</p>
                </div>

                <div class="flex gap-2">
                    <x-ui.input 
                        readonly 
                        value="{{ auth()->user()->referral_link }}" 
                        class="flex-1 bg-white dark:bg-neutral-800 font-mono text-[10px]" 
                        id="referral-link"
                    />
                    <x-ui.button 
                        type="button"
                        variant="outline"
                        x-on:click="
                            navigator.clipboard.writeText('{{ auth()->user()->referral_link }}');
                            $dispatch('toast', { type: 'success', message: 'Referral link copied!' });
                        "
                    >
                        {{ __('Copy') }}
                    </x-ui.button>
                </div>
            </div>

            <div class="flex items-center gap-6 pt-4">
                <x-ui.button variant="primary" type="submit" class="h-14 px-12 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20" data-test="update-profile-button">
                    {{ __('Save Changes') }}
                </x-ui.button>

                <x-action-message class="text-xs font-bold text-success uppercase tracking-widest" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings::delete-user-form />
    </x-settings.layout>
</section>
