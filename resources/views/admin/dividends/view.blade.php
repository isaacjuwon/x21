<?php

use App\Livewire\Concerns\HasToast;
use App\Models\Dividend;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public Dividend $dividend;

    public function mount(Dividend $dividend)
    {
        $this->dividend = $dividend;
    }

    public function processPayout()
    {
        if ($this->dividend->paid_out) {
            return;
        }

        try {
            $this->dividend->processPayout();
            $this->toastSuccess('Dividend payout processed successfully.');
            $this->dividend->refresh();
        } catch (\Exception $e) {
            $this->toastError('Failed to process payout: '.$e->getMessage());
        }
    }

    public function delete()
    {
        if ($this->dividend->paid_out) {
            $this->toastError('Cannot delete a dividend that has been paid out.');

            return;
        }

        $this->dividend->delete();
        $this->toastSuccess('Dividend deleted successfully.');

        return redirect()->route('admin.dividends.index');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-2xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Dividend Details" 
        :description="'Dividend #' . $dividend->id"
    >
        <x-slot:actions>
            <div class="flex items-center gap-3">
                @if(!$dividend->paid_out)
                    <x-ui.button 
                        type="button" 
                        wire:click="processPayout" 
                        wire:confirm="Are you sure you want to process the payout for this dividend? This will create payment records for all eligible shareholders."
                        variant="success" 
                    >
                        Process Payout
                    </x-ui.button>

                    <x-ui.button 
                        type="button" 
                        wire:click="delete" 
                        wire:confirm="Are you sure you want to delete this dividend declaration?"
                        variant="danger" 
                        outline
                    >
                        Delete
                    </x-ui.button>
                @endif
                <x-ui.button tag="a" href="{{ route('admin.dividends.index') }}" variant="outline">

<div class="max-w-2xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Dividend Details" 
        :description="'Dividend #' . $dividend->id"
    >
        <x-slot:actions>
            <div class="flex items-center gap-3">
                @if(!$dividend->paid_out)
                    <x-ui.button 
                        type="button" 
                        wire:click="processPayout" 
                        wire:confirm="Are you sure you want to process the payout for this dividend? This will create payment records for all eligible shareholders."
                        variant="success" 
                    >
                        Process Payout
                    </x-ui.button>

                    <x-ui.button 
                        type="button" 
                        wire:click="delete" 
                        wire:confirm="Are you sure you want to delete this dividend declaration?"
                        variant="danger" 
                        outline
                    >
                        Delete
                    </x-ui.button>
                @endif
                <x-ui.button tag="a" href="{{ route('admin.dividends.index') }}" variant="outline">
                    Back
                </x-ui.button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
        <h3 class="text-xs font-bold text-neutral-500 uppercase tracking-widest mb-4">Declaration Details</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6 text-sm">
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Type</dt>
                <dd class="font-bold text-neutral-900 dark:text-white text-sm">{{ ucfirst($dividend->type) }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Status</dt>
                <dd>
                    <x-ui.badge :color="$dividend->paid_out ? 'success' : 'warning'">
                        {{ $dividend->paid_out ? 'Paid Out' : 'Pending' }}
                    </x-ui.badge>
                </dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Amount Per Share</dt>
                <dd class="font-bold text-neutral-900 dark:text-white text-sm">{{ Number::currency($dividend->amount_per_share) }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Declaration Date</dt>
                <dd class="font-bold text-neutral-900 dark:text-white text-sm">{{ $dividend->declaration_date->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Ex-Dividend Date</dt>
                <dd class="font-bold text-neutral-900 dark:text-white text-sm">{{ $dividend->ex_dividend_date->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Record Date</dt>
                <dd class="font-bold text-neutral-900 dark:text-white text-sm">{{ $dividend->record_date->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Payment Date</dt>
                <dd class="font-bold text-neutral-900 dark:text-white text-sm">{{ $dividend->payment_date->format('M d, Y') }}</dd>
            </div>
        </dl>
    </div>

    @if($dividend->paid_out)
        <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
            <h3 class="text-xs font-bold text-neutral-500 uppercase tracking-widest mb-4">Payout Statistics</h3>
            @php
                $stats = $dividend->getPayoutStatistics();
            @endphp
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6 text-sm">
                <div>
                    <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Total Shareholders</dt>
                    <dd class="font-bold text-neutral-900 dark:text-white text-sm">{{ number_format($stats['total_shareholders']) }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Total Shares</dt>
                    <dd class="font-bold text-neutral-900 dark:text-white text-sm">{{ number_format($stats['total_shares']) }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Total Payout</dt>
                    <dd class="font-bold text-neutral-900 dark:text-white text-sm">{{ Number::currency($stats['total_payout']) }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Avg. Per Shareholder</dt>
                    <dd class="font-bold text-neutral-900 dark:text-white text-sm">{{ Number::currency($stats['average_per_shareholder']) }}</dd>
                </div>
            </dl>
        </div>
    @endif
</div>
