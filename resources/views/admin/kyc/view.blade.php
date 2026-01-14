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
                <h3 class="text-lg font-semibold">User Information</h3>
            </x-slot:header>

            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Name</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $this->verification->user->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $this->verification->user->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $this->verification->user->phone_number ?? 'N/A' }}</p>
                </div>
            </div>
        </x-ui.card>

        <!-- Verification Information -->
        <x-ui.card class="md:col-span-1">
            <x-slot:header>
                <h3 class="text-lg font-semibold">Verification Information</h3>
            </x-slot:header>

            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Type</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                        {{ strtoupper($this->verification->type) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Mode</p>
                    <x-ui.badge :color="$this->verification->verification_mode === VerificationMode::Automatic ? 'info' : 'warning'">
                        {{ ucfirst($this->verification->verification_mode->value) }}
                    </x-ui.badge>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Submitted</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $this->verification->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- KYC Details -->
    <x-ui.card>
        <x-slot:header>
            <h3 class="text-lg font-semibold">KYC Details</h3>
        </x-slot:header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">ID Number</p>
                <p class="font-mono font-medium text-lg text-gray-900 dark:text-white">{{ $this->verification->id_number }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Date of Birth</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $this->verification->dob?->format('M d, Y') ?? 'Not provided' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $this->verification->phone ?? 'Not provided' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $this->verification->email ?? 'Not provided' }}</p>
            </div>
        </div>

        @if($this->verification->document_path)
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Document</p>
                <a href="{{ route('kyc.download-document', $this->verification) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
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
                <h3 class="text-lg font-semibold">API Response</h3>
            </x-slot:header>

            <pre class="bg-gray-100 dark:bg-gray-900 rounded p-4 text-xs overflow-x-auto text-gray-700 dark:text-gray-300">{{ json_encode($this->verification->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </x-ui.card>
    @endif

    <!-- Status Update Section -->
    <x-ui.card>
        <x-slot:header>
            <h3 class="text-lg font-semibold">Update Status</h3>
        </x-slot:header>

        <form wire:submit="updateStatus" class="space-y-4">
            <x-ui.field>
                <x-ui.label>Verification Status</x-ui.label>
                <x-ui.select wire:model="status" required>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="failed">Failed</option>
                </x-ui.select>
                <x-ui.error name="status" />
            </x-ui.field>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('admin.kyc.index') }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    Back
                </a>
                <x-ui.button type="submit" variant="primary">
                    Update Status
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
