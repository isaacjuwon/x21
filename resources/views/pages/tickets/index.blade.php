<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function render()
    {
        return $this->view([
            'tickets' => Auth::user()->tickets()->latest()->paginate(10),
        ])
        ->title('Support Tickets')
        ->layout('layouts::app');
    }
};
?>

<div class="max-w-7xl mx-auto p-6">
    <x-page-header heading="Support Tickets" description="View and manage your support requests">
        <x-slot:actions>
            <x-ui.button wire:navigate href="{{ route('tickets.create') }}">Create Ticket</x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-6">
        <x-ui.card>
            @if ($tickets->isEmpty())
                <div class="text-center py-12 bg-neutral-50/50 dark:bg-neutral-900/20 rounded-[--radius-box] border border-dashed border-neutral-100 dark:border-neutral-700">
                    <x-ui.icon name="inbox" class="w-12 h-12 text-neutral-200 dark:text-neutral-700 mx-auto mb-4" />
                    <p class="text-xs font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">No tickets found</p>
                    <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-2 uppercase tracking-widest">You haven't created any support tickets yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-neutral-50 dark:bg-neutral-900/50 text-neutral-500 dark:text-neutral-400 uppercase tracking-widest text-[10px] font-bold">
                            <tr class="border-b border-neutral-100 dark:border-neutral-700">
                                <th class="px-6 py-4">Subject</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Priority</th>
                                <th class="px-6 py-4">Last Updated</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                            @foreach ($tickets as $ticket)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/50 transition-colors">
                                    <td class="px-6 py-5 text-xs font-bold text-neutral-900 dark:text-white">{{ $ticket->subject }}</td>
                                    <td class="px-6 py-5">
                                        <span @class([
                                            'px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest',
                                            'bg-success/10 text-success' => $ticket->status === 'open',
                                            'bg-neutral-100 text-neutral-500 dark:bg-neutral-700 dark:text-neutral-400' => $ticket->status === 'closed',
                                        ])>
                                            {{ $ticket->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span @class([
                                            'px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest',
                                            'bg-error/10 text-error' => $ticket->priority === 'high',
                                            'bg-amber-100 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' => $ticket->priority === 'medium',
                                            'bg-primary/10 text-primary' => $ticket->priority === 'low',
                                        ])>
                                            {{ $ticket->priority }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-xs font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">{{ $ticket->updated_at->diffForHumans() }}</td>
                                    <td class="px-6 py-5 text-right">
                                        <x-ui.button variant="outline" size="xs" wire:navigate href="{{ route('tickets.show', $ticket) }}" class="rounded-[--radius-field] font-bold uppercase tracking-widest">
                                            View
                                        </x-ui.button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $tickets->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</div>
