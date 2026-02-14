<?php

namespace App\Livewire\Admin\Enquiries;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $status = null;

    #[Computed]
    public function enquiries()
    {
        return Enquiry::query()
            ->with('property')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhereHas('property', function($pq) {
                      $pq->where('title', 'like', '%' . $this->search . '%');
                  });
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function statuses()
    {
        return EnquiryStatus::cases();
    }

    public function updateStatus(Enquiry $enquiry, string $status)
    {
        $enquiry->update(['status' => $status]);
        // $this->dispatch('toast', ['type' => 'success', 'message' => 'Status updated']);
    }

    public function delete(Enquiry $enquiry)
    {
        $enquiry->delete();
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Property Enquiries</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">View and manage messages from potential buyers/renters</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <x-ui.input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search by name, email or property..." 
                type="search"
            >
                <x-slot:leading>
                    <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-gray-400" />
                </x-slot:leading>
            </x-ui.input>
        </div>
        <div>
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
                        <th class="px-6 py-4">Sender</th>
                        <th class="px-6 py-4">Property</th>
                        <th class="px-6 py-4">Message</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($this->enquiries as $enquiry)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-neutral-900 dark:text-white">{{ $enquiry->name }}</p>
                                <p class="text-[10px] text-neutral-500">{{ $enquiry->email }}</p>
                                @if($enquiry->phone)
                                    <p class="text-[10px] text-neutral-400">{{ $enquiry->phone }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($enquiry->property)
                                    <a href="{{ route('admin.properties.view', $enquiry->property) }}" class="text-primary hover:underline font-medium">
                                        {{ str($enquiry->property->title)->limit(30) }}
                                    </a>
                                @else
                                    <span class="text-neutral-400">Deleted Property</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="line-clamp-2 text-neutral-600 dark:text-neutral-300">
                                    {{ $enquiry->message }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$enquiry->status->getColor()">
                                    {{ $enquiry->status->getLabel() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-neutral-500">
                                {{ $enquiry->created_at->format('M d, Y') }}
                                <br>
                                <span class="text-[10px]">{{ $enquiry->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.dropdown portal position="bottom-end">
                                        <x-slot:button>
                                            <x-ui.button variant="ghost" size="sm" icon:variant="min" icon="ellipsis-horizontal" />
                                        </x-slot:button>
                                        <x-slot:menu class="w-48">
                                            @if($enquiry->status !== EnquiryStatus::Responded)
                                                <x-ui.dropdown.item wire:click="updateStatus({{ $enquiry->id }}, 'responded')" icon="check-circle">
                                                    Mark Responded
                                                </x-ui.dropdown.item>
                                            @endif
                                            @if($enquiry->status !== EnquiryStatus::Closed)
                                                <x-ui.dropdown.item wire:click="updateStatus({{ $enquiry->id }}, 'closed')" icon="x-circle">
                                                    Close Enquiry
                                                </x-ui.dropdown.item>
                                            @endif
                                            <x-ui.dropdown.separator />
                                            <x-ui.dropdown.item wire:click="delete({{ $enquiry->id }})" wire:confirm="Permanently delete this enquiry?" icon="trash" variant="danger">
                                                Delete
                                            </x-ui.dropdown.item>
                                        </x-slot:menu>
                                    </x-ui.dropdown>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mb-3" />
                                    <p>No enquiries found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $this->enquiries->links() }}
        </div>
    </div>
</div>
