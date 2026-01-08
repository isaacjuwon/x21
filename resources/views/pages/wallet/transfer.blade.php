<?php

use App\Actions\Wallet\TransferFundAction;
use App\Livewire\Concerns\HasToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    use HasToast;

    #[Rule('required|string|regex:/^[0-9]{11}$/')]
    public string $phone_number = '';

    #[Rule('required|numeric|min:1')]
    public float|int $amount = 0;

    #[Rule('nullable|string|max:255')]
    public ?string $notes = null;

    public ?array $recipientData = null;

    public function validateRecipient(TransferFundAction $transferAction)
    {
        $this->validate([
            'phone_number' => 'required|string|regex:/^[0-9]{11}$/',
        ]);

        $result = $transferAction->validateRecipient($this->phone_number);

        if ($result->isError()) {
            $this->toastError($result->error->getMessage());
            $this->recipientData = null;

            return;
        }

        $this->recipientData = $result->unwrap();
        $this->toastSuccess('Recipient found: '.$this->recipientData['name']);
    }

    public function openConfirmModal()
    {
        $this->validate();

        if (! $this->recipientData) {
            $this->toastError('Please validate recipient first.');

            return;
        }

        $this->dispatch('open-modal', id: 'confirm-transfer');
    }

    public function confirmTransfer(TransferFundAction $transferAction)
    {
        $this->validate();

        $data = [
            'phone_number' => $this->phone_number,
            'amount' => $this->amount,
            'notes' => $this->notes,
        ];

        $result = $transferAction->handle(auth()->user(), $data);

        if ($result->isError()) {
            $this->dispatch('close-modal', id: 'confirm-transfer');
            $this->toastError($result->error->getMessage());

            return;
        }

        $response = $result->unwrap();

        $this->dispatch('close-modal', id: 'confirm-transfer');
        $this->toastSuccess($response['message']);

        // Reset form
        $this->reset(['phone_number', 'amount', 'notes', 'recipientData']);

        // Redirect to wallet index
        return $this->redirect(route('wallet.index'));
    }
}; ?>

<div class="max-w-xl mx-auto p-6">
    <x-page-header 
        heading="Transfer Funds" 
        description="Transfer funds to another user via phone number"
    />

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form wire:submit="openConfirmModal" class="space-y-6">
            <x-ui.field>
                <x-ui.label>{{ __('Recipient Phone Number') }}</x-ui.label>
                <div class="flex gap-2">
                    <x-ui.input 
                        wire:model="phone_number" 
                        type="text"
                        autofocus
                        placeholder="08012345678"
                        class="flex-1"
                     />
                    <x-ui.button 
                        type="button" 
                        wire:click="validateRecipient" 
                        variant="outline"
                    >
                        Verify
                    </x-ui.button>
                </div>
                <x-ui.error name="phone_number" />
                @if($recipientData)
                    <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                        ✓ {{ $recipientData['name'] }}
                    </p>
                @endif
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('Amount (NGN)') }}</x-ui.label>
                <x-ui.input 
                    wire:model="amount" 
                    type="number"
                    min="1"
                    placeholder="Enter amount to transfer"
                 />
                <x-ui.error name="amount" />
                <p class="text-xs text-gray-500 mt-1">Minimum transfer amount is ₦1</p>
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('Notes (Optional)') }}</x-ui.label>
                <x-ui.textarea 
                    wire:model="notes" 
                    placeholder="Add a note for this transfer"
                    rows="3"
                 />
                <x-ui.error name="notes" />
            </x-ui.field>

            <div class="flex justify-end gap-3">
                <x-ui.button tag="a" href="{{ route('wallet.index') }}" variant="outline">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" :disabled="!$recipientData">
                    Transfer Funds
                </x-ui.button>
            </div>
        </form>
    </div>

    {{-- Confirmation Modal --}}
    <x-ui.modal 
        id="confirm-transfer"
        heading="Confirm Transfer" 
        description="Please review the transfer details before confirming"
        width="md"
    >
        <div class="space-y-4">
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Recipient:</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $recipientData['name'] ?? '' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Phone:</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $phone_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Amount:</span>
                    <span class="font-bold text-xl text-green-600 dark:text-green-400">
                        {{ Number::currency($amount) }}
                    </span>
                </div>
                @if($notes)
                <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Notes:</p>
                    <p class="text-sm text-gray-900 dark:text-white mt-1">{{ $notes }}</p>
                </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button type="button" x-on:click="$dispatch('close-modal', {id: 'confirm-transfer'})" variant="outline">
                    Cancel
                </x-ui.button>
                <x-ui.button type="button" wire:click="confirmTransfer" variant="primary">
                    Confirm Transfer
                </x-ui.button>
            </div>
        </div>
    </x-ui.modal>
</div>
