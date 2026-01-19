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
                <div class="text-center py-12">
                    <div class="text-gray-400 mb-2">No tickets found</div>
                    <div class="text-sm text-gray-500">You haven't created any support tickets yet.</div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 font-semibold">Subject</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 font-semibold">Priority</th>
                                <th class="px-4 py-3 font-semibold">Last Updated</th>
                                <th class="px-4 py-3 font-semibold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($tickets as $ticket)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $ticket->subject }}</td>
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
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $ticket->updated_at->diffForHumans() }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <x-ui.button variant="outline" size="sm" wire:navigate href="{{ route('tickets.show', $ticket) }}">
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
