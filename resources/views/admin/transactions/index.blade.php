<?php

use App\Enums\Transaction\Status;
use App\Enums\Transaction\Type;
use App\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $status = null;

    public ?string $type = null;

    #[Computed]
    public function transactions()
    {
        return Transaction::query()
            ->with('user')
            ->when($this->search, function ($q) {
                $q->where('reference', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function statuses()
    {
        return Status::cases();
    }

    #[Computed]
    public function types()
    {
        return Type::cases();
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Transactions</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">View system transactions</p>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-4">
        <div class="w-full md:w-1/3">
            <x-ui.input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search reference or user..." 
                type="search"
            >
                <x-slot:leading>
                    <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-gray-400" />
                </x-slot:leading>
            </x-ui.input>
        </div>
        <div class="w-full md:w-1/4">
            <x-ui.select wire:model.live="status">
                <x-ui.select.option value="">All Statuses</x-ui.select.option>
                @foreach($this->statuses as $s)
                    <x-ui.select.option value="{{ $s->value }}">{{ $s->getLabel() }}</x-ui.select.option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="w-full md:w-1/4">
            <x-ui.select wire:model.live="type">
                <x-ui.select.option value="">All Types</x-ui.select.option>
                @foreach($this->types as $t)
                    <x-ui.select.option value="{{ $t->value }}">{{ $t->getLabel() }}</x-ui.select.option>
                @endforeach
            </x-ui.select>
        </div>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr>
                        <th class="px-6 py-4">Reference</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($this->transactions as $transaction)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4 font-mono text-[10px] text-neutral-900 dark:text-white">
                                {{ $transaction->reference }}
                            </td>
                            <td class="px-6 py-4 text-neutral-900 dark:text-white font-bold">
                                {{ $transaction->user->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">
                                {{ $transaction->type->getLabel() }}
                            </td>
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                {{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$transaction->status->getColor()">
                                    {{ $transaction->status->getLabel() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">
                                {{ $transaction->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-ui.button tag="a" href="{{ route('admin.transactions.view', $transaction) }}" variant="ghost" size="sm">
                                    View
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mb-3" />
                                    <p>No transactions found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $this->transactions->links() }}
        </div>
    </div>
</div>
