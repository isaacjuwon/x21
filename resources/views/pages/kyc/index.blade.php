<?php

use App\Actions\Kyc\AutomaticKycVerificationAction;
use App\Actions\Kyc\ManualKycVerificationAction;
use App\Enums\Kyc\KycMethod;
use App\Enums\Kyc\KycStatus;
use App\Enums\Kyc\KycType;
use App\Models\Kyc;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('KYC Verification')] class extends Component {
    use WithFileUploads;

    public string $nin_number = '';
    public $nin_document = null;
    public bool $nin_use_manual = false;

    public string $bvn_number = '';
    public $bvn_document = null;
    public bool $bvn_use_manual = false;

    #[Computed]
    public function ninKyc(): ?Kyc
    {
        return Auth::user()->fresh()->getKyc(KycType::Nin);
    }

    #[Computed]
    public function bvnKyc(): ?Kyc
    {
        return Auth::user()->fresh()->getKyc(KycType::Bvn);
    }

    #[Computed]
    public function isFullyVerified(): bool
    {
        return Auth::user()->fresh()->isKycVerified();
    }

    public function submitNin(): void
    {
        if ($this->nin_use_manual) {
            $this->validate([
                'nin_number' => ['required', 'string', 'digits:11'],
                'nin_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ]);

            app(ManualKycVerificationAction::class)->handle(
                Auth::user(), KycType::Nin, $this->nin_number, $this->nin_document
            );

            Flux::toast(text: __('NIN document submitted. Our team will review it shortly.'), variant: 'success');
        } else {
            $this->validate([
                'nin_number' => ['required', 'string', 'digits:11'],
            ]);

            app(AutomaticKycVerificationAction::class)->handle(
                Auth::user(), KycType::Nin, $this->nin_number
            );

            $kyc = Auth::user()->fresh()->getKyc(KycType::Nin);

            if ($kyc?->status === KycStatus::Verified) {
                Flux::toast(text: __('NIN verified successfully.'), variant: 'success');
            } elseif ($kyc?->status === KycStatus::Rejected) {
                Flux::toast(text: __('Automatic verification failed. Please upload your NIN document manually.'), variant: 'danger');
                $this->nin_use_manual = true;
            } else {
                Flux::toast(text: __('Verification could not be completed. Please try manual upload.'), variant: 'warning');
                $this->nin_use_manual = true;
            }
        }

        $this->nin_number = '';
        $this->nin_document = null;
        unset($this->ninKyc);
    }

    public function submitBvn(): void
    {
        if ($this->bvn_use_manual) {
            $this->validate([
                'bvn_number' => ['required', 'string', 'digits:11'],
                'bvn_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ]);

            app(ManualKycVerificationAction::class)->handle(
                Auth::user(), KycType::Bvn, $this->bvn_number, $this->bvn_document
            );

            Flux::toast(text: __('BVN document submitted. Our team will review it shortly.'), variant: 'success');
        } else {
            $this->validate([
                'bvn_number' => ['required', 'string', 'digits:11'],
            ]);

            app(AutomaticKycVerificationAction::class)->handle(
                Auth::user(), KycType::Bvn, $this->bvn_number
            );

            $kyc = Auth::user()->fresh()->getKyc(KycType::Bvn);

            if ($kyc?->status === KycStatus::Verified) {
                Flux::toast(text: __('BVN verified successfully.'), variant: 'success');
            } elseif ($kyc?->status === KycStatus::Rejected) {
                Flux::toast(text: __('Automatic verification failed. Please upload your BVN document manually.'), variant: 'danger');
                $this->bvn_use_manual = true;
            } else {
                Flux::toast(text: __('Verification could not be completed. Please try manual upload.'), variant: 'warning');
                $this->bvn_use_manual = true;
            }
        }

        $this->bvn_number = '';
        $this->bvn_document = null;
        unset($this->bvnKyc);
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="max-w-2xl mx-auto space-y-6 animate-pulse">
            <div class="h-8 w-48 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
            <div class="h-48 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
            <div class="h-48 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
        </div>
        HTML;
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <flux:heading size="xl">{{ __('KYC Verification') }}</flux:heading>
        <flux:subheading>{{ __('Verify your identity to unlock all platform features.') }}</flux:subheading>
    </div>

    @if($this->isFullyVerified)
        <flux:callout icon="check-circle" color="green">
            <flux:callout.heading>Identity Fully Verified</flux:callout.heading>
            <flux:callout.text>Your NIN and BVN have been verified. You have full access to all features.</flux:callout.text>
        </flux:callout>
    @endif

    {{-- NIN --}}
    <flux:card class="space-y-4 border-zinc-200 dark:border-zinc-800">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">{{ __('NIN Verification') }}</flux:heading>
                <flux:text class="text-zinc-500 text-sm">{{ __('National Identification Number') }}</flux:text>
            </div>
            <x-kyc.badge :type="App\Enums\Kyc\KycType::Nin" />
        </div>

        @if($this->ninKyc?->status === KycStatus::Verified)
            <flux:callout icon="check-circle" color="green" variant="secondary">
                <flux:callout.text>NIN verified on {{ $this->ninKyc->verified_at->format('M j, Y') }}.</flux:callout.text>
            </flux:callout>

        @elseif($this->ninKyc?->status === KycStatus::Pending && $this->ninKyc?->method === KycMethod::Manual)
            <flux:callout icon="clock" color="yellow" variant="secondary">
                <flux:callout.heading>Document Under Review</flux:callout.heading>
                <flux:callout.text>Your NIN document has been submitted and is awaiting admin review. You'll be notified once it's processed.</flux:callout.text>
            </flux:callout>

        @else
            @if($this->ninKyc?->status === KycStatus::Rejected)
                <flux:callout icon="x-circle" color="red" variant="secondary">
                    <flux:callout.text>{{ $this->ninKyc->rejection_reason ?? 'Verification failed.' }}</flux:callout.text>
                </flux:callout>
            @endif

            <form wire:submit="submitNin" class="space-y-4">
                <flux:input
                    wire:model="nin_number"
                    :label="__('NIN Number')"
                    placeholder="12345678901"
                    maxlength="11"
                    description="{{ __('Enter your 11-digit National Identification Number') }}"
                />

                @if(!$nin_use_manual)
                    <div class="flex items-center gap-2">
                        <flux:checkbox wire:model.live="nin_use_manual" id="nin_manual" />
                        <label for="nin_manual" class="text-sm text-zinc-600 dark:text-zinc-400 cursor-pointer">
                            {{ __('Automatic verification not working? Upload document manually instead') }}
                        </label>
                    </div>
                @else
                    <flux:callout icon="information-circle" color="blue" variant="secondary">
                        <flux:callout.text>Manual verification requires admin review. You'll be notified once approved.</flux:callout.text>
                    </flux:callout>

                    <flux:input
                        wire:model="nin_document"
                        type="file"
                        accept=".jpg,.jpeg,.png,.pdf"
                        :label="__('Upload NIN Document')"
                        description="{{ __('Upload a clear photo or scan of your NIN slip or National ID card. Max 5MB.') }}"
                    />

                    <flux:button variant="ghost" size="sm" wire:click="$set('nin_use_manual', false)">
                        {{ __('← Try automatic verification instead') }}
                    </flux:button>
                @endif

                @error('nin_number') <flux:text class="text-red-500 text-sm">{{ $message }}</flux:text> @enderror
                @error('nin_document') <flux:text class="text-red-500 text-sm">{{ $message }}</flux:text> @enderror

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submitNin">
                        {{ $nin_use_manual ? __('Submit Document for Review') : __('Verify NIN Automatically') }}
                    </span>
                    <span wire:loading wire:target="submitNin">{{ __('Processing...') }}</span>
                </flux:button>
            </form>
        @endif
    </flux:card>

    {{-- BVN --}}
    <flux:card class="space-y-4 border-zinc-200 dark:border-zinc-800">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">{{ __('BVN Verification') }}</flux:heading>
                <flux:text class="text-zinc-500 text-sm">{{ __('Bank Verification Number') }}</flux:text>
            </div>
            <x-kyc.badge :type="App\Enums\Kyc\KycType::Bvn" />
        </div>

        @if($this->bvnKyc?->status === KycStatus::Verified)
            <flux:callout icon="check-circle" color="green" variant="secondary">
                <flux:callout.text>BVN verified on {{ $this->bvnKyc->verified_at->format('M j, Y') }}.</flux:callout.text>
            </flux:callout>

        @elseif($this->bvnKyc?->status === KycStatus::Pending && $this->bvnKyc?->method === KycMethod::Manual)
            <flux:callout icon="clock" color="yellow" variant="secondary">
                <flux:callout.heading>Document Under Review</flux:callout.heading>
                <flux:callout.text>Your BVN document has been submitted and is awaiting admin review. You'll be notified once it's processed.</flux:callout.text>
            </flux:callout>

        @else
            @if($this->bvnKyc?->status === KycStatus::Rejected)
                <flux:callout icon="x-circle" color="red" variant="secondary">
                    <flux:callout.text>{{ $this->bvnKyc->rejection_reason ?? 'Verification failed.' }}</flux:callout.text>
                </flux:callout>
            @endif

            <form wire:submit="submitBvn" class="space-y-4">
                <flux:input
                    wire:model="bvn_number"
                    :label="__('BVN Number')"
                    placeholder="12345678901"
                    maxlength="11"
                    description="{{ __('Enter your 11-digit Bank Verification Number') }}"
                />

                @if(!$bvn_use_manual)
                    <div class="flex items-center gap-2">
                        <flux:checkbox wire:model.live="bvn_use_manual" id="bvn_manual" />
                        <label for="bvn_manual" class="text-sm text-zinc-600 dark:text-zinc-400 cursor-pointer">
                            {{ __('Automatic verification not working? Upload document manually instead') }}
                        </label>
                    </div>
                @else
                    <flux:callout icon="information-circle" color="blue" variant="secondary">
                        <flux:callout.text>Manual verification requires admin review. You'll be notified once approved.</flux:callout.text>
                    </flux:callout>

                    <flux:input
                        wire:model="bvn_document"
                        type="file"
                        accept=".jpg,.jpeg,.png,.pdf"
                        :label="__('Upload BVN Document')"
                        description="{{ __('Upload a clear photo or scan of your bank statement or BVN slip. Max 5MB.') }}"
                    />

                    <flux:button variant="ghost" size="sm" wire:click="$set('bvn_use_manual', false)">
                        {{ __('← Try automatic verification instead') }}
                    </flux:button>
                @endif

                @error('bvn_number') <flux:text class="text-red-500 text-sm">{{ $message }}</flux:text> @enderror
                @error('bvn_document') <flux:text class="text-red-500 text-sm">{{ $message }}</flux:text> @enderror

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submitBvn">
                        {{ $bvn_use_manual ? __('Submit Document for Review') : __('Verify BVN Automatically') }}
                    </span>
                    <span wire:loading wire:target="submitBvn">{{ __('Processing...') }}</span>
                </flux:button>
            </form>
        @endif
    </flux:card>
</div>
