<?php

use App\Actions\Wallet\TransferFundAction;
use App\Livewire\Concerns\HasToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
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

    <div data-slot="card" class="p-6 bg-background-content rounded-3xl border border-border shadow-sm">
        <form wire:submit="openConfirmModal" class="space-y-6">
            <x-ui.field>
                <x-ui.label>{{ __('Recipient Phone Number') }}</x-ui.label>
                <div class="flex gap-2">
                    <x-ui.input 
                        wire:model="phone_number" 
                        type="text"
                        autofocus
                        placeholder="08012345678"
                        class="flex-1 bg-background"
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
                    <p class="text-sm text-success mt-1">
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
                    class="bg-background"
                 />
                <x-ui.error name="amount" />
                <p class="text-[10px] text-foreground-content mt-1 font-bold uppercase tracking-wider">Minimum transfer amount is ₦1</p>
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('Notes (Optional)') }}</x-ui.label>
                <x-ui.textarea 
                    wire:model="notes" 
                    placeholder="Add a note for this transfer"
                    rows="3"
                    class="bg-background"
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
            <div class="bg-background rounded-2xl p-4 space-y-3 border border-border">
                <div class="flex justify-between text-sm">
                    <span class="text-foreground-content">Recipient:</span>
                    <span class="font-bold text-foreground">{{ $recipientData['name'] ?? '' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-foreground-content">Phone:</span>
                    <span class="font-bold text-foreground">{{ $phone_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-foreground-content">Amount:</span>
                    <span class="font-black text-xl text-success">
                        {{ Number::currency($amount) }}
                    </span>
                </div>
                @if($notes)
                <div class="pt-2 border-t border-border">
                    <p class="text-[10px] text-foreground-content font-bold uppercase tracking-wider">Notes:</p>
                    <p class="text-sm text-foreground mt-1">{{ $notes }}</p>
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
