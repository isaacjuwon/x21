<?php

use App\Models\Transaction;
use App\Models\Wallet;
use App\Enums\Wallets\TransactionType;
use App\Enums\Wallets\WalletType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Wallet'), Defer] class extends Component {
    use WithPagination;

    #[Url(as: 'page')]
    public $page = 1;

    #[Url]
    public $sortBy = 'created_at';

    #[Url]
    public $sortDirection = 'desc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function wallet(): Wallet
    {
        return Auth::user()->getWallet(WalletType::General);
    }

    #[Computed]
    public function transactions()
    {
        return Auth::user()->transactions()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="space-y-6 animate-pulse">
            <div class="flex items-center justify-between">
                <div class="h-8 w-32 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                <div class="flex space-x-2">
                    <div class="h-10 w-24 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                    <div class="h-10 w-24 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="h-24 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
                <div class="h-24 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
                <div class="h-24 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
            </div>
            <div class="h-64 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
        </div>
        HTML;
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('My Wallet') }}</flux:heading>

        <div class="flex space-x-2">
            <flux:button href="{{ route('wallet.transfer') }}" variant="outline" icon="plus" wire:navigate>
                {{ __('Transfer') }}
            </flux:button>
            <flux:button href="{{ route('wallet.withdraw') }}" variant="primary" icon="banknotes" wire:navigate>
                {{ __('Withdraw') }}
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <flux:card class="space-y-2 border-zinc-200 dark:border-zinc-800">
            <flux:heading size="sm" class="text-zinc-500">{{ __('Total Balance') }}</flux:heading>
            <flux:text size="xl" weight="semibold">
                {{ Number::currency($this->wallet->balance) }}
            </flux:text>
        </flux:card>

        <flux:card class="space-y-2 border-zinc-200 dark:border-zinc-800">
            <flux:heading size="sm" class="text-zinc-500">{{ __('Available Balance') }}</flux:heading>
            <flux:text size="xl" weight="semibold">
                {{ Number::currency($this->wallet->available_balance) }}
            </flux:text>
        </flux:card>

        <flux:card class="space-y-2 border-zinc-200 dark:border-zinc-800">
            <flux:heading size="sm" class="text-zinc-500">{{ __('Held Balance') }}</flux:heading>
            <flux:text size="xl" weight="semibold">
                {{ Number::currency($this->wallet->held_balance) }}
            </flux:text>
        </flux:card>
    </div>

    <flux:card class="p-0 overflow-hidden border-zinc-200 dark:border-zinc-800">
        <flux:table :paginate="$this->transactions">
            <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'amount'" :direction="$sortDirection" wire:click="sort('amount')" align="end">{{ __('Amount') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Reference') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->transactions as $transaction)
                    <flux:table.row :key="$transaction->id">
                        <flux:table.cell class="text-zinc-500 whitespace-nowrap">
                            {{ $transaction->created_at->format('M j, Y H:i') }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:icon :name="$transaction->type->getIcon()" class="size-4 text-zinc-400" />
                                <span>{{ $transaction->type->getLabel() }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell align="end" variant="strong" class="{{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->amount > 0 ? '+' : '' }}{{ Number::currency($transaction->amount) }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge :color="$transaction->status->getColor()" size="sm" inset="top bottom">
                                {{ $transaction->status->getLabel() }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500 text-sm font-mono">
                            {{ $transaction->reference }}
                        </flux:table.cell>
                    </flux:row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
