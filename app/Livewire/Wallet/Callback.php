<?php

namespace App\Livewire\Wallet;

use App\Enums\Transaction\Status;
use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Callback extends Component
{
    #[Url]
    public ?string $reference = null;
    
    #[Url]
    public ?string $trxref = null;

    public function mount()
    {
        // Paystack sends 'trxref' and 'reference'
        $this->reference = $this->reference ?? $this->trxref;
    }

    public function checkStatus()
    {
        if (!$this->reference) {
            return;
        }

        $transaction = Transaction::where('reference', $this->reference)->first();

        if (!$transaction) {
            return;
        }

        if ($transaction->status === Status::Success) {
            $this->redirect(route('wallet.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.wallet.callback');
    }
}
