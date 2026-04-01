<?php

use App\Actions\Tickets\ReplyToTicketAction;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Ticket Details')] class extends Component {
    public Ticket $ticket;

    #[Validate('required|string|min:5')]
    public string $reply = '';

    public function mount(Ticket $ticket): void
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403);
        }
        $this->ticket = $ticket;
    }

    #[Computed]
    public function replies()
    {
        return $this->ticket->replies()->with('user')->oldest()->get();
    }

    public function sendReply(): void
    {
        $this->validate();

        app(ReplyToTicketAction::class)->handle(
            $this->ticket,
            Auth::user(),
            $this->reply,
        );

        $this->reply = '';
        $this->ticket->refresh();
        unset($this->replies);
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="max-w-3xl mx-auto space-y-6 animate-pulse">
            <div class="h-8 w-48 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
            <div class="h-64 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
        </div>
        HTML;
    }
}; ?>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <flux:button :href="route('tickets.index')" variant="ghost" icon="arrow-left" inset="left" wire:navigate />
            <div>
                <flux:heading size="xl">{{ $ticket->subject }}</flux:heading>
                <flux:text class="text-zinc-500 text-sm">Ticket #{{ $ticket->id }} &middot; Opened {{ $ticket->created_at->diffForHumans() }}</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:badge :color="$ticket->priority->getFluxColor()" size="sm">{{ $ticket->priority->getLabel() }}</flux:badge>
            <flux:badge :color="$ticket->status->getFluxColor()" :icon="$ticket->status->getFluxIcon()" size="sm">{{ $ticket->status->getLabel() }}</flux:badge>
        </div>
    </div>

    {{-- Original message --}}
    <flux:card class="space-y-3 border-zinc-200 dark:border-zinc-800">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <flux:avatar :name="$ticket->user->name" size="sm" />
                <flux:text class="font-medium text-sm">{{ $ticket->user->name }}</flux:text>
            </div>
            <flux:text class="text-zinc-400 text-xs">{{ $ticket->created_at->format('M j, Y H:i') }}</flux:text>
        </div>
        <flux:text class="text-sm leading-relaxed whitespace-pre-wrap">{{ $ticket->message }}</flux:text>
    </flux:card>

    {{-- Replies --}}
    @foreach ($this->replies as $reply)
        <flux:card
            :class="$reply->is_staff
                ? 'border-violet-200 dark:border-violet-800 bg-violet-50 dark:bg-violet-950/20'
                : 'border-zinc-200 dark:border-zinc-800'"
            class="space-y-3"
            wire:key="reply-{{ $reply->id }}"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <flux:avatar :name="$reply->user->name" size="sm" />
                    <flux:text class="font-medium text-sm">{{ $reply->user->name }}</flux:text>
                    @if($reply->is_staff)
                        <flux:badge color="violet" size="sm">Support</flux:badge>
                    @endif
                </div>
                <flux:text class="text-zinc-400 text-xs">{{ $reply->created_at->format('M j, Y H:i') }}</flux:text>
            </div>
            <flux:text class="text-sm leading-relaxed whitespace-pre-wrap">{{ $reply->message }}</flux:text>
        </flux:card>
    @endforeach

    {{-- Reply form (only if ticket is open) --}}
    @if($ticket->status->isOpen())
        <flux:card class="space-y-4 border-zinc-200 dark:border-zinc-800">
            <flux:heading size="sm">{{ __('Add a Reply') }}</flux:heading>
            <form wire:submit="sendReply" class="space-y-4">
                <flux:textarea
                    wire:model="reply"
                    placeholder="{{ __('Type your reply...') }}"
                    rows="4"
                />
                @error('reply')
                    <flux:text class="text-red-500 text-sm">{{ $message }}</flux:text>
                @enderror
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('Send Reply') }}</span>
                        <span wire:loading>{{ __('Sending...') }}</span>
                    </flux:button>
                </div>
            </form>
        </flux:card>
    @else
        <flux:callout icon="check-circle" color="green" variant="secondary">
            <flux:callout.text>This ticket is {{ $ticket->status->getLabel() }} and no longer accepting replies.</flux:callout.text>
        </flux:callout>
    @endif
</div>
