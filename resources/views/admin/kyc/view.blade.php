<?php

use App\Models\KycVerification;
use App\Enums\Kyc\Status as KycStatusEnum;
use App\Enums\Kyc\VerificationMode;
use App\Livewire\Concerns\HasToast;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public KycVerification $verification;

    #[Rule('required|in:pending,verified,failed')]
    public string $status = '';

    public function mount(KycVerification $verification)
    {
        $this->verification = $verification;
        $this->status = $verification->status->value;
    }

    public function updateStatus()
    {
        $this->validate();

        $statusEnum = KycStatusEnum::from($this->status);
        $this->verification->update(['status' => $statusEnum]);

        // If manual verification is approved, mark user as verified
        if ($this->verification->verification_mode === VerificationMode::Manual && $statusEnum === KycStatusEnum::Verified) {
            $this->verification->user->markAsVerified();
        }

        $this->toastSuccess('KYC status updated successfully.');
    }

    public function render()
    {
        return $this->view()
            ->title('KYC Verification')
            ->layout('layouts::admin');
    }
}; ?>

<div class="max-w-4xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="KYC Verification Details" 
        description="View and manage verification details"
    />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- User Information -->
        <x-ui.card class="md:col-span-1">
            <x-slot:header>
                <h3 class="text-base font-bold text-neutral-900 dark:text-white">User Information</h3>
            </x-slot:header>

            <div class="space-y-4">
                <div>
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Name</p>
                    <p class="font-bold text-neutral-900 dark:text-white text-sm">{{ $this->verification->user->name }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Email</p>
                    <p class="font-bold text-neutral-900 dark:text-white text-sm">{{ $this->verification->user->email }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Phone</p>
                    <p class="font-bold text-neutral-900 dark:text-white text-sm">{{ $this->verification->user->phone_number ?? 'N/A' }}</p>
                </div>
            </div>
        </x-ui.card>

        <!-- Verification Information -->
        <x-ui.card class="md:col-span-1">
            <x-slot:header>
                <h3 class="text-base font-bold text-neutral-900 dark:text-white">Verification Information</h3>
            </x-slot:header>

            <div class="space-y-4">
                <div>
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Type</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary">
                        {{ strtoupper($this->verification->type) }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Mode</p>
                    <x-ui.badge :color="$this->verification->verification_mode === VerificationMode::Automatic ? 'info' : 'warning'" class="text-[10px]">
                        {{ ucfirst($this->verification->verification_mode->value) }}
                    </x-ui.badge>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Submitted</p>
                    <p class="font-bold text-neutral-900 dark:text-white text-sm">{{ $this->verification->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- KYC Details -->
    <x-ui.card>
        <x-slot:header>
            <h3 class="text-base font-bold text-neutral-900 dark:text-white">KYC Details</h3>
        </x-slot:header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">ID Number</p>
                <p class="font-mono font-bold text-lg text-neutral-900 dark:text-white">{{ $this->verification->id_number }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Date of Birth</p>
                <p class="font-bold text-neutral-900 dark:text-white text-sm">{{ $this->verification->dob?->format('M d, Y') ?? 'Not provided' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Phone</p>
                <p class="font-bold text-neutral-900 dark:text-white text-sm">{{ $this->verification->phone ?? 'Not provided' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Email</p>
                <p class="font-bold text-neutral-900 dark:text-white text-sm">{{ $this->verification->email ?? 'Not provided' }}</p>
            </div>
        </div>

        @if($this->verification->document_path)
            <div class="mt-6 pt-6 border-t border-neutral-100 dark:border-neutral-700">
                <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-3">Document</p>
                <a href="{{ route('kyc.download-document', $this->verification) }}" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-[--radius-field] hover:bg-primary-600">
                    <x-ui.icon name="arrow-down-tray" class="w-4 h-4 mr-2" />
                    Download Document
                </a>
            </div>
        @endif
    </x-ui.card>

    <!-- Verification Response (for Automatic) -->
    @if($this->verification->verification_mode === VerificationMode::Automatic && $this->verification->response)
        <x-ui.card>
            <x-slot:header>
                <h3 class="text-base font-bold text-neutral-900 dark:text-white">API Response</h3>
            </x-slot:header>

            <pre class="bg-neutral-100 dark:bg-neutral-900 rounded-[--radius-field] p-4 text-xs overflow-x-auto text-neutral-700 dark:text-neutral-300">{{ json_encode($this->verification->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </x-ui.card>
    @endif

    <!-- Status Update Section -->
    <x-ui.card>
        <x-slot:header>
            <h3 class="text-base font-bold text-neutral-900 dark:text-white">Update Status</h3>
        </x-slot:header>

        <form wire:submit="updateStatus" class="space-y-4">
            <x-ui.field>
                <x-ui.label>Verification Status</x-ui.label>
                <x-ui.select wire:model="status" required>
                    <x-ui.select.option value="pending">Pending</x-ui.select.option>
                    <x-ui.select.option value="verified">Verified</x-ui.select.option>
                    <x-ui.select.option value="failed">Failed</x-ui.select.option>
                </x-ui.select>
                <x-ui.error name="status" />
            </x-ui.field>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('admin.kyc.index') }}" class="px-4 py-2 text-neutral-700 dark:text-neutral-300 border border-neutral-300 dark:border-neutral-600 rounded-[--radius-field] hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                    Back
                </a>
                <x-ui.button type="submit" variant="primary">
                    Update Status
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
