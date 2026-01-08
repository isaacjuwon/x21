<?php

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

    public function render()
    {
        return $this->view()
            ->title('Ticket #' . $this->ticket->id)
            ->layout('layouts::app');
    }
};
?>

<div class="max-w-4xl mx-auto p-6">
    <x-page-header 
        heading="Ticket #{{ $ticket->id }}" 
        description="{{ $ticket->subject }}"
        back-url="{{ route('tickets.index') }}"
        back-label="Back to Tickets"
    >
        <x-slot:actions>
             <span @class([
                'px-3 py-1 rounded-full text-sm font-semibold',
                'bg-green-100 text-green-700' => $ticket->status === 'open',
                'bg-gray-100 text-gray-700' => $ticket->status === 'closed',
            ])>
                {{ ucfirst($ticket->status) }}
            </span>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="md:col-span-2 space-y-6">
            <!-- Ticket Message -->
            <x-ui.card>
                <div class="flex items-center gap-3 mb-4">
                     <x-ui.avatar :src="$ticket->user->profile_photo_url" :alt="$ticket->user->name" />
                     <div>
                        <div class="font-bold text-gray-900">{{ $ticket->user->name }}</div>
                        <div class="text-xs text-gray-500">{{ $ticket->created_at->format('M d, Y H:i') }}</div>
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
                        'bg-white border border-gray-100' => $reply->user_id !== Auth::id(),
                        'bg-blue-50 text-blue-900' => $reply->user_id === Auth::id(),
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
            @if($ticket->status === 'open')
                <x-ui.card class="bg-gray-50 border-gray-200">
                    <form wire:submit="reply">
                        <x-ui.field>
                            <x-ui.label for="reply">Add a Reply</x-ui.label>
                            <x-ui.textarea wire:model="message" id="reply" rows="3" placeholder="Type your reply..." />
                            <x-ui.error name="message" />
                        </x-ui.field>
                        <div class="flex justify-end mt-3">
                            <x-ui.button type="submit">Send Reply</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            @else
                <div class="text-center p-4 bg-gray-100 rounded-lg text-gray-500">
                    This ticket is closed. You cannot reply to it.
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Ticket ID</div>
                        <div class="font-mono text-sm">#{{ $ticket->id }}</div>
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
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Created</div>
                        <div class="text-sm">{{ $ticket->created_at->format('M d, Y H:i A') }}</div>
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
