<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

new class extends Component
{
    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(
        DisableTwoFactorAuthentication $disableTwoFactorAuthentication,
    ): void {
        abort_unless(
            Features::enabled(Features::twoFactorAuthentication()),
            Response::HTTP_FORBIDDEN,
        );

        if (
            Fortify::confirmsTwoFactorAuthentication() &&
            is_null(auth()->user()->two_factor_confirmed_at)
        ) {
            $disableTwoFactorAuthentication(auth()->user());
        }

        $this->twoFactorEnabled = auth()
            ->user()
            ->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(
            Features::twoFactorAuthentication(),
            'confirm',
        );
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enable(
        EnableTwoFactorAuthentication $enableTwoFactorAuthentication,
    ): void {
        $enableTwoFactorAuthentication(auth()->user());

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()
                ->user()
                ->hasEnabledTwoFactorAuthentication();
        }

        $this->loadSetupData();

        $this->showModal = true;
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user();

        try {
            $this->qrCodeSvg = $user?->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(
        ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication,
    ): void {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->closeModal();

        $this->twoFactorEnabled = true;
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(
        DisableTwoFactorAuthentication $disableTwoFactorAuthentication,
    ): void {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showModal',
            'showVerificationStep',
        );

        $this->resetErrorBag();

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()
                ->user()
                ->hasEnabledTwoFactorAuthentication();
        }
    }

    /**
     * Get the current modal configuration state.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => __('Two-Factor Authentication Enabled'),
                'description' => __(
                    'Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.',
                ),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify Authentication Code'),
                'description' => __(
                    'Enter the 6-digit code from your authenticator app.',
                ),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable Two-Factor Authentication'),
            'description' => __(
                'To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.',
            ),
            'buttonText' => __('Continue'),
        ];
    }

    public function render()
    {
        return $this->view()
            ->title('Two-Factor Authentication')
            ->layout('layouts::app');
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout
        :heading="__('Two Factor Authentication')"
        :subheading="__('Manage your two-factor authentication settings')"
    >
        <div class="flex flex-col w-full mx-auto space-y-6" wire:cloak>
            @if ($twoFactorEnabled)
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <x-ui.badge color="success" class="text-[10px] font-bold uppercase tracking-widest">{{ __('Enabled') }}</x-ui.badge>
                    </div>

                    <p class="text-xs font-bold text-neutral-600 dark:text-neutral-400 leading-relaxed tracking-wide">
                        {{ __('With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                    </p>

                    <livewire:settings.two-factor.recovery-codes :$requiresConfirmation/>

                    <div class="flex justify-start">
                        <x-ui.button
                            variant="danger"
                            icon="shield-exclamation"
                            icon:variant="outline"
                            wire:click="disable"
                            class="h-12 px-8 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-error/20"
                        >
                            {{ __('Disable 2FA') }}
                        </x-ui.button>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <x-ui.badge color="danger" class="text-[10px] font-bold uppercase tracking-widest">{{ __('Disabled') }}</x-ui.badge>
                    </div>

                    <p class="text-xs font-bold text-neutral-500 dark:text-neutral-400 leading-relaxed tracking-wide">
                        {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                    </p>

                    <x-ui.button
                        variant="primary"
                        icon="shield-check"
                        icon:variant="outline"
                        wire:click="enable"
                        class="h-12 px-8 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20"
                    >
                        {{ __('Enable 2FA') }}
                    </x-ui.button>
                </div>
            @endif
        </div>
    </x-settings.layout>

    <x-ui.modal
        name="two-factor-setup-modal"
        class="max-w-md md:min-w-md"
        @close="closeModal"
        wire:model="showModal"
    >
        <div class="space-y-6">
            <div class="flex flex-col items-center space-y-4">
                <div class="p-0.5 w-auto rounded-full border border-neutral-100 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm">
                    <div class="p-2.5 rounded-full border border-neutral-200 dark:border-neutral-700 overflow-hidden bg-neutral-50 dark:bg-neutral-900 relative">
                        <div class="flex items-stretch absolute inset-0 w-full h-full divide-x [&>div]:flex-1 divide-neutral-200 dark:divide-neutral-700 justify-around opacity-50">
                            @for ($i = 1; $i <= 5; $i++)
                                <div></div>
                            @endfor
                        </div>

                        <div class="flex flex-col items-stretch absolute w-full h-full divide-y [&>div]:flex-1 inset-0 divide-neutral-200 dark:divide-neutral-700 justify-around opacity-50">
                            @for ($i = 1; $i <= 5; $i++)
                                <div></div>
                            @endfor
                        </div>

                        <x-ui.icon name="qr-code" class="relative z-20 dark:text-accent-foreground"/>
                    </div>
                </div>

                <div class="space-y-2 text-center">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ $this->modalConfig['title'] }}</h3>
                    <p class="text-xs font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">{{ $this->modalConfig['description'] }}</p>
                </div>
            </div>

            @if ($showVerificationStep)
                <div class="space-y-6">
                    <div class="flex flex-col items-center space-y-3">
                        <x-input-otp
                            :digits="6"
                            name="code"
                            wire:model="code"
                            autocomplete="one-time-code"
                        />
                        @error('code')
                            <p class="text-xs font-bold text-error uppercase tracking-widest">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="flex items-center space-x-3">
                        <x-ui.button
                            variant="outline"
                            class="flex-1 h-12 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs"
                            wire:click="resetVerification"
                        >
                            {{ __('Back') }}
                        </x-ui.button>

                        <x-ui.button
                            variant="primary"
                            class="flex-1 h-12 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20"
                            wire:click="confirmTwoFactor"
                            x-bind:disabled="$wire.code.length < 6"
                        >
                            {{ __('Confirm') }}
                        </x-ui.button>
                    </div>
                </div>
            @else
                @error('setupData')
                    <x-ui.callout variant="danger" icon="x-circle" heading="{{ $message }}"/>
                @enderror

                <div class="flex justify-center">
                    <div class="relative w-64 overflow-hidden border border-neutral-100 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900 rounded-[--radius-box] aspect-square">
                        @empty($qrCodeSvg)
                            <div class="absolute inset-0 flex items-center justify-center bg-white dark:bg-neutral-800 animate-pulse">
                                <x-ui.icon name="loading" class="w-8 h-8 text-primary animate-spin" />
                            </div>
                        @else
                            <div class="flex items-center justify-center h-full p-4">
                                <div class="bg-white p-4 rounded-[--radius-field] shadow-sm">
                                    {!! $qrCodeSvg !!}
                                </div>
                            </div>
                        @endempty
                    </div>
                </div>

                <div>
                    <x-ui.button
                        :disabled="$errors->has('setupData')"
                        variant="primary"
                        class="w-full h-12 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20"
                        wire:click="showVerificationIfNecessary"
                    >
                        {{ $this->modalConfig['buttonText'] }}
                    </x-ui.button>
                </div>

                <div class="space-y-4">
                    <div class="relative flex items-center justify-center w-full">
                        <div class="absolute inset-0 w-full h-px top-1/2 bg-neutral-100 dark:bg-neutral-700"></div>
                        <span class="relative px-3 text-[10px] font-bold bg-white dark:bg-neutral-800 text-neutral-400 uppercase tracking-widest">
                            {{ __('or, enter the code manually') }}
                        </span>
                    </div>

                    <div
                        class="flex items-center space-x-2"
                        x-data="{
                            copied: false,
                            async copy() {
                                try {
                                    await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 1500);
                                } catch (e) {
                                    console.warn('Could not copy to clipboard');
                                }
                            }
                        }"
                    >
                        <div class="flex items-stretch w-full border border-neutral-100 dark:border-neutral-700 rounded-[--radius-box] bg-neutral-50 dark:bg-neutral-900/50 overflow-hidden">
                            @empty($manualSetupKey)
                                <div class="flex items-center justify-center w-full p-4">
                                    <x-ui.icon name="loading" class="w-5 h-5 text-neutral-300 animate-spin" />
                                </div>
                            @else
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $manualSetupKey }}"
                                    class="w-full p-4 bg-transparent outline-none text-xs font-bold font-mono tracking-widest text-neutral-900 dark:text-white"
                                />

                                <button
                                    type="button"
                                    @click="copy()"
                                    class="px-5 transition-colors border-l border-neutral-100 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <x-ui.icon name="document-duplicate" x-show="!copied" class="w-4 h-4 text-neutral-500" />
                                    <x-ui.icon name="check" x-show="copied" class="w-4 h-4 text-success" />
                                </button>
                            @endempty
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-ui.modal>
</section>
