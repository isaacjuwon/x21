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

    public $type = 'bvn';

    public $id_number = '';

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
        $kyc = $user->kycVerifications()->latest()->first();
        if ($kyc) {
            $this->type = $kyc->type->value;
            $this->id_number = $kyc->id_number;
            $this->status = $kyc->status instanceof KycStatusEnum ? $kyc->status : KycStatusEnum::match($kyc->status);
            $this->response = $kyc->response;
        }
    }

    public function submit(CreateKycVerificationAction $action)
    {
        $rules = [
            'type' => 'required|in:bvn,nin',
            'id_number' => 'required|string',
            'dob' => 'nullable|date',
            'phone' => 'nullable|phone',
            'email' => 'nullable|email',
        ];

        // Add document validation for manual mode
        if ($this->verificationMode === VerificationMode::Manual) {
            $rules['document'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'; // 5MB max
        }

        $this->validate($rules);

        $user = auth()->user();
        $data = [
            'type' => $this->type,
            'id_number' => $this->id_number,
            'dob' => $this->dob,
            'phone' => $this->phone,
            'email' => $this->email,
        ];

        // Handle document upload for manual verification
        if ($this->verificationMode === VerificationMode::Manual && $this->document) {
            $documentPath = $this->document->store("kyc/{$user->id}", 'private');
            $data['document_path'] = $documentPath;
        }

        // Create KYC record via action
        $kyc = $action->handle($user, $data);

        // Automatic mode: Trigger verification in parent index component
        if ($this->verificationMode === VerificationMode::Automatic) {
            $this->dispatch('trigger-verify', kycId: $kyc->id);
            $this->toastSuccess('KYC record created! Verification started...');
        } else {
            // Manual mode: Notify user of admin review
            $this->toastSuccess('KYC submitted! Admin is reviewing your details.');
        }

        $this->reset(['id_number', 'dob', 'phone', 'email', 'document']);
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
            <x-ui.fieldset label="Verification Information">
                

                <x-ui.field>
                    <x-ui.label>Type</x-ui.label>
                    <x-ui.select wire:model.live="type">
                        @foreach(KycType::cases() as $case)
                            <x-ui.select.option :value="$case->value">{{ $case->getLabel() }}</x-ui.select.option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>{{ $type === 'bvn' ? 'BVN Number' : 'NIN Number' }}</x-ui.label>
                    <x-ui.input wire:model.live="id_number" type="text" required />
                    <x-ui.error name="id_number" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Date of Birth (optional)</x-ui.label>
                    <x-ui.input wire:model.live="dob" type="date" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Phone (optional)</x-ui.label>
                    <x-ui.input wire:model.live="phone" type="text" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Email (optional)</x-ui.label>
                    <x-ui.input wire:model.live="email" type="email" />
                </x-ui.field>

                @if ($verificationMode === \App\Enums\VerificationMode::Manual)
                    <x-ui.field>
                        <x-ui.label>Upload Document (required)</x-ui.label>
                        <x-ui.input 
                            wire:model.live="document" 
                            type="file" 
                            accept=".pdf,.jpg,.jpeg,.png"
                        />
                        <x-ui.error name="document" />
                        <p class="text-sm text-gray-500 mt-2">Accepted formats: PDF, JPG, PNG (Max 5MB)</p>
                    </x-ui.field>
                @endif
            </x-ui.fieldset>

            <div class="flex gap-3">
                <x-ui.button type="submit" class="flex-1">
                    Submit Verification
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
</div>
