<?php

use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $status = 'all';

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Ticket::query()->with('user')->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $this->view([
            'tickets' => $query->paginate(10),
        ])
        ->title('Manage Tickets')
        ->layout('layouts::admin');
    }
};
?>

<div class="p-6 space-y-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Support Tickets</h1>
        <div class="flex items-center gap-2">
            <span class="text-xs text-neutral-600">Filter by Status:</span>
            <x-ui.select wire:model.live="status" class="w-40">
                <x-ui.select.option value="all">All Tickets</x-ui.select.option>
                <x-ui.select.option value="open">Open</x-ui.select.option>
                <x-ui.select.option value="closed">Closed</x-ui.select.option>
            </x-ui.select>
        </div>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr class="border-b border-neutral-100 dark:border-neutral-700">
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Subject</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Created</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-[10px] text-neutral-500 dark:text-neutral-400">#{{ $ticket->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-neutral-900 dark:text-white">{{ $ticket->user->name }}</div>
                                <div class="text-[10px] text-neutral-500 dark:text-neutral-400">{{ $ticket->user->email }}</div>
                            </td>
                            <td class="px-4 py-3 text-neutral-900 dark:text-white">{{ $ticket->subject }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'px-2 py-0.5 rounded-full text-[10px] font-bold',
                                    'bg-success/10 text-success' => $ticket->status === 'open',
                                    'bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300' => $ticket->status === 'closed',
                                ])>
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                             <td class="px-4 py-3">
                                <span @class([
                                    'px-2 py-0.5 rounded-full text-[10px] font-bold',
                                    'bg-error/10 text-error' => $ticket->priority === 'high',
                                    'bg-warning/10 text-warning' => $ticket->priority === 'medium',
                                    'bg-primary/10 text-primary' => $ticket->priority === 'low',
                                ])>
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400">{{ $ticket->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-ui.button variant="ghost" size="sm" wire:navigate href="{{ route('admin.tickets.show', $ticket) }}">
                                    Manage
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-neutral-500 dark:text-neutral-400">No tickets found matching your filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $tickets->links() }}
        </div>
    </x-ui.card>
</div>
