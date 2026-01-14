<?php

use App\Actions\Kyc\VerificationAction;
use App\Livewire\Concerns\HasToast;
use App\Models\KycVerification;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use HasToast, WithPagination;

    public string $search = '';

    // Form properties
    public string $type = 'bvn';

    public string $id_number = '';

    public string $dob = '';

    public string $phone = '';

    public string $email = '';

    public function render()
    {
        return $this->view()
            ->title('KYC Verifications')
            ->layout('layouts::app');
    }

    #[Livewire\Attributes\On('trigger-verify')]
    public function verify(VerificationAction $verificationAction, $kycId)
    {
        $kyc = KycVerification::find($kycId);
        if (! $kyc) {
             return;
        }
        $result = $verificationAction->handle($kyc);

        if ($result->isOk()) {
            $kyc = $result->unwrap();
            $this->toastSuccess('Verification request submitted!');
        } elseif ($result->isError()) {
            $this->toastError($result->error?->getMessage() ?? 'Verification failed.');
        }
    }

    #[Computed]
    public function verifications()
    {
        $user = auth()->user();

        return KycVerification::query()
            ->where('user_id', $user->id)
            ->when($this->search, fn ($q) => $q->where('id_number', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(10);
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="My KYC Verifications" 
        description="Manage your identity verifications"
    >
        <x-slot name="actions">
            <x-ui.modal.trigger id="kyc-verification-modal">
                <x-ui.button icon="plus" variant="primary">
                    Add KYC Verification
                </x-ui.button>
            </x-ui.modal.trigger>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($this->verifications as $verification)
            <x-ui.card class="flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                            {{ strtoupper($verification->type) }}
                        </span>
                        <x-ui.badge :color="$verification->status === KycStatusEnum::Verified ? 'success' : ($verification->status === KycStatusEnum::Failed ? 'danger' : 'warning')">
                            {{ ucfirst($verification->status->value) }}
                        </x-ui.badge>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">ID Number</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white font-mono">{{ $verification->id_number }}</p>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Created {{ $verification->created_at->diffForHumans() }}
                    </div>
                </div>

                <div class="mt-4">
                    @if($verification->verification_mode === \App\Enums\Kyc\VerificationMode::Automatic)
                        @if($verification->status !== KycStatusEnum::Verified)
                            <x-ui.button 
                                wire:click="verify({{ $verification->id }})" 
                                size="sm"
                                class="w-full"
                            >
                                Verify Now
                            </x-ui.button>
                        @else
                            <x-ui.button 
                                disabled
                                size="sm"
                                variant="outline"
                                class="w-full"
                            >
                                ✓ Verified
                            </x-ui.button>
                        @endif
                    @else
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-2">
                            Pending manual review
                        </p>
                    @endif
                </div>
            </x-ui.card>
        @empty
            <div class="col-span-full">
                <div class="rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-12 text-center">
                    <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-500 dark:text-gray-400">No KYC verifications yet</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Add a new verification to get started</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($this->verifications->count() > 0)
        <div class="flex justify-center">
            {{ $this->verifications->links() }}
        </div>
    @endif

   <livewire:pages::kyc.create />
</div>
