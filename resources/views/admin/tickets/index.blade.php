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

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Support Tickets</h1>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">Filter by Status:</span>
            <x-ui.select wire:model.live="status" class="w-40">
                <x-ui.select.option value="all">All Tickets</x-ui.select.option>
                <x-ui.select.option value="open">Open</x-ui.select.option>
                <x-ui.select.option value="closed">Closed</x-ui.select.option>
            </x-ui.select>
        </div>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-4 py-3 font-semibold text-gray-700">ID</th>
                        <th class="px-4 py-3 font-semibold text-gray-700">User</th>
                        <th class="px-4 py-3 font-semibold text-gray-700">Subject</th>
                        <th class="px-4 py-3 font-semibold text-gray-700">Status</th>
                        <th class="px-4 py-3 font-semibold text-gray-700">Priority</th>
                        <th class="px-4 py-3 font-semibold text-gray-700">Created</th>
                        <th class="px-4 py-3 font-semibold text-gray-700 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-gray-500">#{{ $ticket->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $ticket->user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $ticket->user->email }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-900">{{ $ticket->subject }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    'bg-green-100 text-green-700' => $ticket->status === 'open',
                                    'bg-gray-100 text-gray-700' => $ticket->status === 'closed',
                                ])>
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                             <td class="px-4 py-3">
                                <span @class([
                                    'px-2 py-1 rounded-full text-xs font-semibold',
                                    'bg-red-100 text-red-700' => $ticket->priority === 'high',
                                    'bg-yellow-100 text-yellow-700' => $ticket->priority === 'medium',
                                    'bg-blue-100 text-blue-700' => $ticket->priority === 'low',
                                ])>
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $ticket->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-ui.button variant="outline" size="sm" wire:navigate href="{{ route('admin.tickets.show', $ticket) }}">
                                    Manage
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No tickets found matching your filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $tickets->links() }}
        </div>
    </x-ui.card>
</div>
