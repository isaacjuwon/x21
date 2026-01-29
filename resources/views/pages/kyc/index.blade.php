<?php

use App\Actions\Kyc\VerificationAction;
use App\Livewire\Concerns\HasToast;
use App\Enums\Kyc\Status as KycStatusEnum;
use App\Enums\Kyc\Type as KycType;
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
            <x-ui.card class="flex flex-col justify-between shadow-none border-neutral-100 dark:border-neutral-700 bg-white dark:bg-neutral-800">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-neutral-100 dark:bg-neutral-700/50 text-neutral-600 dark:text-neutral-400">
                            {{ $verification->type->getLabel() }}
                        </span>
                        <x-ui.badge :color="$verification->status === KycStatusEnum::Verified ? 'success' : ($verification->status === KycStatusEnum::Failed ? 'danger' : 'warning')" class="text-[10px] font-bold uppercase tracking-widest">
                            {{ ucfirst($verification->status->value) }}
                        </x-ui.badge>
                    </div>
                    
                    <div>
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">ID Number</p>
                        <p class="text-base font-bold text-neutral-900 dark:text-white font-mono tracking-wider">{{ $verification->id_number }}</p>
                    </div>

                    <div class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">
                        Created {{ $verification->created_at->diffForHumans() }}
                    </div>
                </div>

                <div class="mt-4">
                    @if($verification->verification_mode === \App\Enums\Kyc\VerificationMode::Automatic)
                        @if($verification->status !== KycStatusEnum::Verified)
                            <x-ui.button 
                                wire:click="verify({{ $verification->id }})" 
                                size="sm"
                                class="w-full h-11 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs"
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
                <div class="rounded-[--radius-box] border-2 border-dashed border-neutral-100 dark:border-neutral-700 p-12 text-center bg-neutral-50/50 dark:bg-neutral-900/20">
                    <x-ui.icon name="inbox" class="w-12 h-12 text-neutral-200 dark:text-neutral-700 mx-auto mb-4" />
                    <p class="text-xs font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">No KYC verifications yet</p>
                    <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-2 uppercase tracking-widest">Add a new verification to get started</p>
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
