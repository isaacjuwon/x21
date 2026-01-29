<?php

use App\Livewire\Concerns\HasToast;
use App\Settings\VerificationSettings;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public string $kyc_verification_mode = 'automatic';

    public function mount(VerificationSettings $settings): void
    {
        $this->kyc_verification_mode = $settings->kyc_verification_mode;
    }

    public function save(VerificationSettings $settings)
    {
        $settings->kyc_verification_mode = $this->kyc_verification_mode;
        $settings->save();

        $this->toastSuccess('Verification settings updated successfully.');
    }

    public function render()
    {
        return $this->view()
            ->title('Verification Settings')
            ->layout('layouts::admin');
    }
};
?>

<section class="w-full">
    <x-layouts.admin.settings heading="Verification Settings" subheading="Configure KYC and identity verification options">
        <form wire:submit="save" class="mx-6 space-y-8">
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white border-b border-neutral-100 dark:border-neutral-700 pb-2">KYC Verification Mode</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <x-ui.input 
                            type="radio" 
                            name="kyc_verification_mode" 
                            value="automatic"
                            wire:model="kyc_verification_mode"
                            id="mode_automatic"
                        />
                        <x-ui.label for="mode_automatic" class="mb-0">Automatic - Instant API verification via Dojah</x-ui.label>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-ui.input 
                            type="radio" 
                            name="kyc_verification_mode" 
                            value="manual"
                            wire:model="kyc_verification_mode"
                            id="mode_manual"
                        />
                        <x-ui.label for="mode_manual" class="mb-0">Manual - Document submission with admin approval</x-ui.label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <x-ui.button variant="primary" type="submit" class="w-full md:w-auto">
                    Save Changes
                </x-ui.button>
            </div>
        </form>
    </x-layouts.admin.settings>
</section>
