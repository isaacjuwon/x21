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

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6 text-sm">
            <div>
                <dt class="text-gray-500">User</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $transaction->user->name ?? 'System' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Email</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $transaction->user->email ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Amount</dt>
                <dd class="font-medium text-gray-900 dark:text-white text-lg">{{ number_format($transaction->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd>
                    <x-ui.badge :color="$transaction->status->getColor()">
                        {{ $transaction->status->getLabel() }}
                    </x-ui.badge>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Type</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $transaction->type->getLabel() }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Date</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $transaction->created_at->format('M d, Y H:i:s') }}</dd>
            </div>
            <div class="col-span-1 md:col-span-2">
                <dt class="text-gray-500">Description</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $transaction->description }}</dd>
            </div>
            
            @if($transaction->meta)
                <div class="col-span-1 md:col-span-2 border-t border-gray-100 dark:border-gray-700 pt-4 mt-2">
                    <dt class="text-gray-500 mb-2">Metadata</dt>
                    <dd class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 font-mono text-xs overflow-x-auto">
                        <pre>{{ json_encode($transaction->meta, JSON_PRETTY_PRINT) }}</pre>
                    </dd>
                </div>
            @endif
        </dl>
    </div>
</div>
