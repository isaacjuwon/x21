<?php

use App\Enums\ShareStatus;
use App\Models\Share;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $status = null;

    #[Computed]
    public function shares()
    {
        return Share::query()
            ->with('holder')
            ->when($this->search, function ($q) {
                $q->whereHasMorph('holder', ['App\Models\User'], function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function statuses()
    {
        return ShareStatus::cases();
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Shares</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Manage user shares</p>
        </div>
        <x-ui.button icon="plus"  tag="a" href="{{ route('admin.shares.create') }}" variant="primary">
            New Share   
        </x-ui.button>
    </div>

    <div class="flex flex-col md:flex-row gap-4">
        <div class="w-full md:w-1/2">
            <x-ui.input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search by holder..." 
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
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr>
                        <th class="px-6 py-4">Holder</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Quantity</th>
                        <th class="px-6 py-4">Value</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($this->shares as $share)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                {{ $share->holder->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">
                                <x-ui.badge color="neutral">
                                    {{ class_basename($share->holder_type) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                {{ number_format($share->quantity) }} Units
                            </td>
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                {{ Number::currency($share->quantity * $shareSettings->share_price, 'NGN') }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$share->status->getColor()">
                                    {{ $share->status->getLabel() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">
                                {{ $share->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-ui.button tag="a" href="{{ route('admin.shares.view', $share) }}" variant="ghost" size="sm">
                                    View
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mb-3" />
                                    <p>No shares found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $this->shares->links() }}
        </div>
    </div>
</div>
