<?php

namespace App\Livewire\Wallet;

use App\Actions\Wallet\TransferFundAction;
use App\Livewire\Concerns\HasToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('layouts.app')]
class Transfer extends Component
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

    public function render()
    {
        return view('livewire.wallet.transfer');
    }
}
