<?php

use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Component;

new class extends Component
{
    public Transaction $transaction;

    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-3xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Transaction Details" 
        :description="'Reference: ' . $transaction->reference"
    >
        <x-slot:actions>
            <x-ui.button tag="a" href="{{ route('admin.transactions.index') }}" variant="outline">
                Back
            </x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6 text-sm">
            <div>
                <dt class="text-xs text-neutral-500">User</dt>
                <dd class="font-bold text-neutral-900 dark:text-white">{{ $transaction->user->name ?? 'System' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-neutral-500">Email</dt>
                <dd class="font-bold text-neutral-900 dark:text-white">{{ $transaction->user->email ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-neutral-500">Amount</dt>
                <dd class="font-extrabold text-neutral-900 dark:text-white text-base">{{ number_format($transaction->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-neutral-500">Status</dt>
                <dd>
                    <x-ui.badge :color="$transaction->status->getColor()">
                        {{ $transaction->status->getLabel() }}
                    </x-ui.badge>
                </dd>
            </div>
            <div>
                <dt class="text-xs text-neutral-500">Type</dt>
                <dd class="font-bold text-neutral-900 dark:text-white">{{ $transaction->type->getLabel() }}</dd>
            </div>
            <div>
                <dt class="text-xs text-neutral-500">Date</dt>
                <dd class="font-bold text-neutral-900 dark:text-white">{{ $transaction->created_at->format('M d, Y H:i:s') }}</dd>
            </div>
            <div class="col-span-1 md:col-span-2">
                <dt class="text-xs text-neutral-500">Description</dt>
                <dd class="font-bold text-neutral-900 dark:text-white">{{ $transaction->description }}</dd>
            </div>
            
            @if($transaction->meta)
                <div class="col-span-1 md:col-span-2 border-t border-neutral-100 dark:border-neutral-700 pt-4 mt-2">
                    <dt class="text-xs text-neutral-500 mb-2">Metadata</dt>
                    <dd class="bg-neutral-50 dark:bg-neutral-900 rounded-[--radius-field] p-4 font-mono text-[10px] overflow-x-auto text-neutral-900 dark:text-white">
                        <pre>{{ json_encode($transaction->meta, JSON_PRETTY_PRINT) }}</pre>
                    </dd>
                </div>
            @endif
        </dl>
    </div>
</div>
