<?php

use App\Enums\Transaction\Status;
use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
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
        if (! $this->reference) {
            return;
        }

        $transaction = Transaction::where('reference', $this->reference)->first();

        if (! $transaction) {
            return;
        }

        if ($transaction->status === Status::Success) {
            $this->redirect(route('wallet.index'), navigate: true);
        }
    }
}; ?>

<div class="min-h-[60vh] flex flex-col items-center justify-center p-6 text-center" wire:poll.2s="checkStatus">
    <div class="mb-8 relative">
        <!-- Animated Icon Container -->
        <div class="relative w-24 h-24 flex items-center justify-center">
            <!-- Ping Effect -->
            <div class="absolute inset-0 bg-primary/20 rounded-full animate-ping"></div>
            
            <!-- Static Background -->
            <div class="absolute inset-0 bg-primary/10 rounded-full"></div>
            
            <!-- Icon -->
            <x-ui.icon name="arrow-path" class="w-10 h-10 text-primary animate-spin" />
        </div>
    </div>

    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        Processing Payment
    </h2>
    
    <p class="text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-8">
        We are verifying your transaction. This usually takes a few seconds. Please do not close this page.
    </p>

    <!-- Transaction Reference Display -->
    @if($reference)
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-full text-xs font-mono text-slate-500 dark:text-slate-400">
            <span>Ref:</span>
            <span class="select-all">{{ $reference }}</span>
        </div>
    @endif
</div>
