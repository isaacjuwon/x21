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
                'px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest',
                'bg-success/10 text-success' => $ticket->status === 'open',
                'bg-neutral-100 text-neutral-500 dark:bg-neutral-700 dark:text-neutral-400' => $ticket->status === 'closed',
            ])>
                {{ $ticket->status }}
            </span>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="md:col-span-2 space-y-6">
            <!-- Ticket Message -->
            <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none">
                <div class="flex items-center gap-4 mb-6">
                     <x-ui.avatar :src="$ticket->user->profile_photo_url" :alt="$ticket->user->name" />
                     <div>
                        <div class="text-xs font-bold text-neutral-900 dark:text-white">{{ $ticket->user->name }}</div>
                        <div class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">{{ $ticket->created_at->format('M d, Y H:i') }}</div>
                     </div>
                </div>
                <div class="prose max-w-none text-xs font-bold text-neutral-600 dark:text-neutral-300 leading-relaxed tracking-wide">
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
                        'max-w-[85%] rounded-[--radius-box] p-5 shadow-sm',
                        'bg-white dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700' => $reply->user_id !== Auth::id(),
                        'bg-primary/5 dark:bg-primary/10 border border-primary/20 dark:border-primary/80' => $reply->user_id === Auth::id(),
                    ])>
                        <div class="flex items-center gap-3 mb-3 {{ $reply->user_id === Auth::id() ? 'justify-end' : '' }}">
                            <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ $reply->user->name }}</span>
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">{{ $reply->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="text-xs font-bold text-neutral-600 dark:text-neutral-300 leading-relaxed tracking-wide whitespace-pre-wrap">{{ $reply->message }}</div>
                    </div>

                    @if($reply->user_id === Auth::id())
                        <x-ui.avatar :src="$reply->user->profile_photo_url" :alt="$reply->user->name" class="flex-shrink-0" />
                    @endif
                </div>
            @endforeach

            <!-- Reply Form -->
            @if($ticket->status === 'open')
                <x-ui.card class="bg-neutral-50 dark:bg-neutral-900/50 border-neutral-100 dark:border-neutral-700 rounded-[--radius-box] shadow-none">
                    <form wire:submit="reply">
                        <x-ui.field>
                            <x-ui.label for="reply" class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Add a Reply</x-ui.label>
                            <x-ui.textarea wire:model="message" id="reply" rows="3" placeholder="Type your reply..." class="font-bold tracking-widest pt-4" />
                            <x-ui.error name="message" />
                        </x-ui.field>
                        <div class="flex justify-end mt-4">
                            <x-ui.button type="submit" class="h-12 px-8 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20">Send Reply</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            @else
                <div class="text-center p-8 bg-neutral-50 dark:bg-neutral-900/50 rounded-[--radius-box] border-2 border-dashed border-neutral-100 dark:border-neutral-700">
                    <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">This ticket is closed. You cannot reply to it.</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <x-ui.card class="bg-white dark:bg-neutral-800 rounded-[--radius-box] border-neutral-100 dark:border-neutral-700 shadow-none">
                <div class="space-y-6">
                    <div>
                        <div class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest mb-1">Ticket ID</div>
                        <div class="font-mono text-xs font-bold text-neutral-900 dark:text-white">#{{ $ticket->id }}</div>
                    </div>
                     <div>
                        <div class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest mb-2">Priority</div>
                         <span @class([
                            'px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest',
                            'bg-error/10 text-error' => $ticket->priority === 'high',
                            'bg-amber-100 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' => $ticket->priority === 'medium',
                            'bg-primary/10 text-primary' => $ticket->priority === 'low',
                        ])>
                            {{ $ticket->priority }}
                        </span>
                    </div>
                     <div>
                        <div class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest mb-1">Created</div>
                        <div class="text-xs font-bold text-neutral-900 dark:text-white">{{ $ticket->created_at->format('M d, Y H:i A') }}</div>
                    </div>
                     <div>
                        <div class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest mb-1">Last Updated</div>
                        <div class="text-xs font-bold text-neutral-900 dark:text-white">{{ $ticket->updated_at->diffForHumans() }}</div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
