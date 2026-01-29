<?php

use App\Models\Referral;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function referrals()
    {
        return Referral::query()
            ->with(['referrer', 'referred'])
            ->when($this->search, function ($query) {
                $query->whereHas('referrer', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhereHas('referred', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(20);
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Referrals</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Track and manage user referrals</p>
        </div>
    </div>

    <div class="w-full max-w-xs">
        <x-ui.input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search referrer or referred..." 
            type="search"
        >
            <x-slot:leading>
                <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-neutral-400" />
            </x-slot:leading>
        </x-ui.input>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr>
                        <th class="px-6 py-4">Referrer</th>
                        <th class="px-6 py-4">Referred User</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($this->referrals as $referral)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                <div class="flex flex-col">
                                    <span>{{ $referral->referrer->name }}</span>
                                    <span class="text-[10px] text-neutral-500 font-normal">{{ $referral->referrer->email }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">
                                @if($referral->referred)
                                    <div class="flex flex-col">
                                        <span class="font-bold text-neutral-900 dark:text-white">{{ $referral->referred->name }}</span>
                                        <span class="text-[10px] font-normal">{{ $referral->referred->email }}</span>
                                    </div>
                                @else
                                    <span class="italic">Pending Registration</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">
                                {{ $referral->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$referral->is_registered ? 'success' : 'warning'" class="text-[10px]">
                                    {{ $referral->is_registered ? 'Registered' : 'Pending' }}
                                </x-ui.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-neutral-500 dark:text-neutral-400">
                                <div class="flex flex-col items-center justify-center p-8">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-neutral-300 mb-2" />
                                    <p>No referrals found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $this->referrals->links() }}
        </div>
    </div>
</div>