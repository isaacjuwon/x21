<?php

use App\Actions\Tickets\CloseTicket;
use App\Actions\Tickets\ReplyToTicket;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public Ticket $ticket;

    #[Validate('required|min:2')]
    public string $message = '';

    public function reply(ReplyToTicket $action)
    {
        $this->validate();

        $action->execute($this->ticket, Auth::user(), $this->message);

        $this->reset('message');
    }

    public function close(CloseTicket $action)
    {
        $action->execute($this->ticket);
    }

    public function render()
    {
        return $this->view()
            ->title('Manage Ticket #' . $this->ticket->id)
            ->layout('layouts::admin');
    }
};
?>

<div class="max-w-4xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <x-ui.button variant="ghost" size="sm" wire:navigate href="{{ route('admin.tickets.index') }}">
                &larr; Back
            </x-ui.button>
            <h1 class="text-2xl font-bold text-gray-800">Ticket #{{ $ticket->id }}</h1>
             <span @class([
                'px-3 py-1 rounded-full text-sm font-semibold',
                'bg-green-100 text-green-700' => $ticket->status === 'open',
                'bg-gray-100 text-gray-700' => $ticket->status === 'closed',
            ])>
                {{ ucfirst($ticket->status) }}
            </span>
        </div>
        @if($ticket->status === 'open')
            <x-ui.button variant="danger" wire:click="close" wire:confirm="Are you sure you want to close this ticket?">
                Close Ticket
            </x-ui.button>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <!-- Ticket Message -->
            <x-ui.card>
                <div class="border-b border-gray-100 pb-4 mb-4">
                     <div class="font-bold text-lg text-gray-900 mb-1">{{ $ticket->subject }}</div>
                     <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span>By {{ $ticket->user->name }}</span>
                        <span>&bull;</span>
                        <span>{{ $ticket->created_at->format('M d, Y H:i A') }}</span>
                     </div>
                </div>
                <div class="prose max-w-none text-gray-700">
                    {{ $ticket->message }}
                </div>
            </x-ui.card>

            <!-- Replies -->
            @foreach ($ticket->replies as $reply)
                <div class="flex gap-4 {{ $reply->user_id === Auth::id() ? 'justify-end' : '' }}">
                    @if($reply->user_id !== Auth::id())
                        <x-ui.avatar :src="$reply->user->profile_photo_url" :alt="$reply->user->name" class="flex-shrink-0" />
                    @endif
                    
                    <div @class([
                        'max-w-[80%] rounded-lg p-4 shadow-sm',
                        'bg-white border border-gray-100' => $reply->user_id !== Auth::id(), // User reply
                        'bg-blue-50 text-blue-900 border border-blue-100' => $reply->user_id === Auth::id(), // Admin reply
                    ])>
                        <div class="flex items-center gap-2 mb-2 {{ $reply->user_id === Auth::id() ? 'justify-end' : '' }}">
                            <span class="font-bold text-sm">{{ $reply->user->name }}</span>
                            <span class="text-xs opacity-70">{{ $reply->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="text-sm whitespace-pre-wrap">{{ $reply->message }}</div>
                    </div>

                    @if($reply->user_id === Auth::id())
                        <x-ui.avatar :src="$reply->user->profile_photo_url" :alt="$reply->user->name" class="flex-shrink-0" />
                    @endif
                </div>
            @endforeach

            <!-- Reply Form -->
            <x-ui.card class="bg-gray-50 border-gray-200">
                <h3 class="font-semibold text-gray-800 mb-4">Post a Reply</h3>
                <form wire:submit="reply">
                    <x-ui.field>
                        <x-ui.textarea wire:model="message" rows="4" placeholder="Type your response..." />
                        <x-ui.error name="message" />
                    </x-ui.field>
                    <div class="flex justify-end mt-3">
                        <x-ui.button type="submit">Send Reply</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <div class="space-y-4">
                     <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">User Details</div>
                        <div class="flex items-center gap-2">
                             <x-ui.avatar :src="$ticket->user->profile_photo_url" :alt="$ticket->user->name" size="sm" />
                             <div>
                                 <div class="text-sm font-medium">{{ $ticket->user->name }}</div>
                                 <div class="text-xs text-gray-500">{{ $ticket->user->email }}</div>
                             </div>
                        </div>
                    </div>
                     <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Priority</div>
                         <span @class([
                            'px-2 py-0.5 rounded text-xs font-semibold',
                            'bg-red-100 text-red-700' => $ticket->priority === 'high',
                            'bg-yellow-100 text-yellow-700' => $ticket->priority === 'medium',
                            'bg-blue-100 text-blue-700' => $ticket->priority === 'low',
                        ])>
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>
                     <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Last Updated</div>
                        <div class="text-sm">{{ $ticket->updated_at->diffForHumans() }}</div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
