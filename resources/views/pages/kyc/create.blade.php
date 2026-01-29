<?php

use App\Actions\Kyc\CreateKycVerificationAction;
use App\Enums\Kyc\Status as KycStatusEnum;
use App\Enums\Kyc\Type as KycType;
use App\Enums\Kyc\VerificationMode;
use App\Livewire\Concerns\HasToast;
use App\Settings\VerificationSettings;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use HasToast, WithFileUploads;

    public $bvn = '';

    public $nin = '';

    public $dob = '';

    public $phone = '';

    public $email = '';

    public $document = null;

    public $status = null;

    public $response = null;

    public $verificationMode;

    public function mount()
    {
        // Get verification mode from settings
        $verificationSettings = app(VerificationSettings::class);
        $this->verificationMode = VerificationMode::tryFrom($verificationSettings->kyc_verification_mode) ?? VerificationMode::Automatic;

        $user = auth()->user();

        $bvnKyc = $user->kycVerifications()->where('type', KycType::Bvn)->latest()->first();
        if ($bvnKyc) {
            $this->bvn = $bvnKyc->id_number;
        }

        $ninKyc = $user->kycVerifications()->where('type', KycType::Nin)->latest()->first();
        if ($ninKyc) {
            $this->nin = $ninKyc->id_number;
        }

        $latestKyc = $user->kycVerifications()->latest()->first();
        if ($latestKyc) {
            $this->status = $latestKyc->status instanceof KycStatusEnum ? $latestKyc->status : KycStatusEnum::match($latestKyc->status);
            $this->response = $latestKyc->response;
        }
    }

    public function submit(CreateKycVerificationAction $action)
    {
        $rules = [
            'bvn' => 'required|string|size:11',
            'nin' => 'required|string|min:10|max:11',
            'dob' => 'nullable|date',
            'phone' => 'nullable',
            'email' => 'nullable|email',
        ];

        // Add document validation for manual mode
        if ($this->verificationMode === VerificationMode::Manual) {
            $rules['document'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'; // 5MB max
        }

        $this->validate($rules);

        $user = auth()->user();
        $commonData = [
            'dob' => $this->dob,
            'phone' => $this->phone,
            'email' => $this->email,
        ];

        // Handle document upload for manual verification
        if ($this->verificationMode === VerificationMode::Manual && $this->document) {
            $documentPath = $this->document->store("kyc/{$user->id}", 'private');
            $commonData['document_path'] = $documentPath;
        }

        // Create or Update BVN record
        $bvn = $action->handle($user, array_merge($commonData, [
            'type' => KycType::Bvn->value,
            'id_number' => $this->bvn,
        ]));

        // Create or Update NIN record
        $nin = $action->handle($user, array_merge($commonData, [
            'type' => KycType::Nin->value,
            'id_number' => $this->nin,
        ]));

        // Automatic mode: Trigger verification for both
        if ($this->verificationMode === VerificationMode::Automatic) {
            $this->dispatch('trigger-verify', kycId: $bvn->id);
            $this->dispatch('trigger-verify', kycId: $nin->id);
            $this->toastSuccess('KYC records created! Verification started for both BVN and NIN.');
        } else {
            // Manual mode: Notify user of admin review
            $this->toastSuccess('KYC submitted! Admin is reviewing your details.');
        }

        $this->reset(['bvn', 'nin', 'dob', 'phone', 'email', 'document']);
        $this->dispatch('close-modal', id: 'kyc-verification-modal');
        $this->dispatch('refresh'); // Refresh list
    }

    public function render()
    {
        return $this->view()
            ->title('KYC Verification')
            ->layout('layouts::app');
    }
};
?>

<div>
    <div class="max-w-2xl mx-auto p-6">
       

        @if ($status)
            <div class="mt-6">
                <x-ui.alerts :type="$status === KycStatusEnum::Verified ? 'success' : ($status === KycStatusEnum::Failed ? 'danger' : 'info')">
                    @if ($status === KycStatusEnum::Verified)
                        Your KYC is verified.
                    @elseif ($status === KycStatusEnum::Failed)
                        Verification failed. Please check your details and try again.
                    @else
                        Verification is pending. Please wait for processing.
                    @endif
                </x-ui.alerts>
            </div>
        @endif
    </div>

    <x-ui.modal 
        id="kyc-verification-modal"
        heading="KYC Verification"
        description="Submit your BVN or NIN for verification"
    >
        <form wire:submit="submit" class="space-y-4">
            <x-ui.fieldset label="Verification Information" class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.field>
                        <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">BVN Number</x-ui.label>
                        <x-ui.input 
                            wire:model.live="bvn" 
                            type="text" 
                            maxlength="11" 
                            required 
                            placeholder="00000000000" 
                            class="text-base font-bold tracking-widest h-14"
                        />
                        <x-ui.error name="bvn" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">NIN Number</x-ui.label>
                        <x-ui.input 
                            wire:model.live="nin" 
                            type="text" 
                            maxlength="11" 
                            required 
                            placeholder="00000000000" 
                            class="text-base font-bold tracking-widest h-14"
                        />
                        <x-ui.error name="nin" />
                    </x-ui.field>
                </div>

                <x-ui.field>
                    <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Date of Birth (optional)</x-ui.label>
                    <x-ui.input wire:model.live="dob" type="date" class="h-12" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Phone (optional)</x-ui.label>
                    <x-ui.input wire:model.live="phone" type="text" class="h-12" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Email (optional)</x-ui.label>
                    <x-ui.input wire:model.live="email" type="email" class="h-12" />
                </x-ui.field>

                @if ($verificationMode === VerificationMode::Manual)
                    <x-ui.field>
                        <x-ui.label class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Upload Document (required)</x-ui.label>
                        <x-ui.input 
                            wire:model.live="document" 
                            type="file" 
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="h-12"
                        />
                        <x-ui.error name="document" />
                        <p class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest mt-2">Accepted formats: PDF, JPG, PNG (Max 5MB)</p>
                    </x-ui.field>
                @endif
            </x-ui.fieldset>

            <div class="flex gap-4 pt-4">
                <x-ui.button type="submit" class="flex-1 h-14 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20">
                    Submit Verification
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
</div>
