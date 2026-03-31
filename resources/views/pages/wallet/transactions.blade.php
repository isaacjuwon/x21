<?php

use App\Enums\Wallets\TransactionStatus;
use App\Enums\Wallets\TransactionType;
use App\Enums\Wallets\WalletType;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Transactions'), Defer] class extends Component {
    use WithPagination;

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    #[Url]
    public string $filterType = '';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $search = '';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function updatedFilterType(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedSearch(): void { $this->resetPage(); }

    #[Computed]
    public function transactions()
    {
        return Auth::user()
            ->getWallet(WalletType::General)
            ->transactions()
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, fn ($q) => $q->where('reference', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);
    }

    #[Computed]
    public function typeOptions(): array
    {
        return collect(TransactionType::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }

    #[Computed]
    public function statusOptions(): array
    {
        return collect(TransactionStatus::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="space-y-6 animate-pulse">
            <div class="h-8 w-48 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
            <div class="flex gap-3">
                <div class="h-10 w-40 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                <div class="h-10 w-40 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                <div class="h-10 flex-1 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
            </div>
            <div class="h-96 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
        </div>
        HTML;
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Transactions') }}</flux:heading>
            <flux:subheading>{{ __('Your complete wallet transaction history.') }}</flux:subheading>
        </div>
        <flux:button href="{{ route('wallet.index') }}" variant="ghost" icon="arrow-left" wire:navigate>
            {{ __('Back to Wallet') }}
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        <flux:select wire:model.live="filterType" placeholder="{{ __('All Types') }}" class="w-40">
            <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
            @foreach ($this->typeOptions as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}" class="w-44">
            <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
            @foreach ($this->statusOptions as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search by reference…') }}"
            icon="magnifying-glass"
            class="flex-1 min-w-48"
        />

        @if ($filterType || $filterStatus || $search)
            <flux:button wire:click="$set('filterType', ''); $set('filterStatus', ''); $set('search', '')" variant="ghost" icon="x-mark">
                {{ __('Clear') }}
            </flux:button>
        @endif
    </div>

    <flux:card class="p-0 overflow-hidden border-zinc-200 dark:border-zinc-800">
        <flux:table :paginate="$this->transactions">
            <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'created_at'"
                    :direction="$sortDirection"
                    wire:click="sort('created_at')"
                >{{ __('Date') }}</flux:table.column>

                <flux:table.column>{{ __('Type') }}</flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'amount'"
                    :direction="$sortDirection"
                    wire:click="sort('amount')"
                    align="end"
                >{{ __('Amount') }}</flux:table.column>

                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Reference') }}</flux:table.column>
                <flux:table.column>{{ __('Notes') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->transactions as $transaction)
                    <flux:table.row :key="$transaction->id">
                        <flux:table.cell class="text-zinc-500 whitespace-nowrap text-sm">
                            {{ $transaction->created_at->format('M j, Y H:i') }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:icon :name="$transaction->type->getIcon()" class="size-4 text-zinc-400" />
                                <span class="text-sm">{{ $transaction->type->getLabel() }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell align="end" variant="strong"
                            class="{{ $transaction->type->isDeposit() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"
                        >
                            {{ $transaction->type->isDeposit() ? '+' : '-' }}{{ Number::currency($transaction->amount) }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge
                                :color="$transaction->status->getFluxColor()"
                                :icon="$transaction->status->getFluxIcon()"
                                size="sm"
                                inset="top bottom"
                            >
                                {{ $transaction->status->getLabel() }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500 text-xs font-mono">
                            {{ $transaction->reference }}
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500 text-sm max-w-xs truncate">
                            {{ $transaction->notes ?? '—' }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="py-12 text-center text-zinc-400">
                            {{ __('No transactions found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
