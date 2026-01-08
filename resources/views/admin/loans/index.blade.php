<?php

use App\Enums\LoanStatus;
use App\Models\Loan;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\HasToast;

new class extends Component
{
    use WithPagination, HasToast;

    public string $search = '';
    public ?string $status = null;

    #[Computed]
    public function loans()
    {
        return Loan::query()
            ->with('user')
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%'.$this->search.'%')))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(10);
    }

    public function delete(Loan $loan)
    {
        $loan->delete();
        $this->toastSuccess('Loan deleted successfully.');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <x-ui.heading>Loans</x-ui.heading>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Manage user loans and repayment statuses.</p>
        </div>
        <x-ui.button tag="a" icon="plus" href="{{ route('admin.loans.create') }}" variant="primary">
            New Loan
        </x-ui.button>
    </div>

    <div class="flex items-center gap-4">
        <div class="w-full max-w-md">
            <x-ui.input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search by user..." 
                type="search"
            >
                <x-slot:leading>
                    <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-neutral-400" />
                </x-slot:leading>
            </x-ui.input>
        </div>
        <div class="w-48">
             <x-ui.select wire:model.live="status" placeholder="Filter by status">
                <x-ui.select.option value="">All Statuses</x-ui.select.option>
                @foreach(LoanStatus::cases() as $status)
                    <x-ui.select.option value="{{ $status->value }}">{{ $status->getLabel() }}</x-ui.select.option>
                @endforeach
            </x-ui.select>
        </div>
    </div>

    <x-ui.table>
        <x-slot:header>
            <x-ui.table.header>User</x-ui.table.header>
            <x-ui.table.header>Amount</x-ui.table.header>
            <x-ui.table.header>Balance</x-ui.table.header>
            <x-ui.table.header>Status</x-ui.table.header>
            <x-ui.table.header class="text-right"></x-ui.table.header>
        </x-slot:header>

        <x-slot:body>
            @forelse($this->loans as $loan)
                <x-ui.table.row wire:key="loan-{{ $loan->id }}">
                    <x-ui.table.cell>
                        <div class="font-medium text-neutral-900 dark:text-white">{{ $loan->user->name }}</div>
                        <div class="text-xs text-neutral-500">{{ $loan->user->email }}</div>
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        {{ Number::currency($loan->amount) }}
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        {{ Number::currency($loan->balance_remaining) }}
                    </x-ui.table.cell>
                    <x-ui.table.cell>
                        <x-ui.badge :color="$loan->status_badge">
                            {{ $loan->status->getLabel() }}
                        </x-ui.badge>
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-right">
                        <x-ui.dropdown>
                            <x-slot:button class="justify-center">
                                <x-ui.button variant="ghost" icon="ellipsis-horizontal" squared />
                            </x-slot:button>
                            
                            <x-slot:menu>
                                <x-ui.dropdown.item :href="route('admin.loans.view', $loan)" icon="eye">
                                    View Details
                                </x-ui.dropdown.item>
                                
                                <x-ui.dropdown.item wire:click="delete({{ $loan->id }})" wire:confirm="Are you sure you want to delete this loan?" icon="trash" variant="danger">
                                    Delete
                                </x-ui.dropdown.item>
                            </x-slot:menu>
                        </x-ui.dropdown>
                    </x-ui.table.cell>
                </x-ui.table.row>
@empty
                <x-ui.table.row>
                    <x-ui.table.cell colspan="5" class="py-12 text-center text-neutral-500">
                        <div class="flex flex-col items-center justify-center">
                            <x-ui.icon name="inbox" class="w-12 h-12 text-neutral-300 mb-3" />
                            <p>No loans found</p>
                        </div>
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforelse
        </x-slot:body>
    </x-ui.table>

    <div class="mt-4">
        {{ $this->loans->links() }}
    </div>
</div>
