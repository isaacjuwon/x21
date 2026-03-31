<?php

use App\Actions\Tickets\OpenTicketAction;
use App\Enums\Tickets\TicketPriority;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Open a Ticket')] class extends Component {
    #[Validate('required|string|max:255')]
    public string $subject = '';

    #[Validate('required|string|min:10')]
    public string $message = '';

    #[Validate('required|in:low,medium,high,urgent')]
    public string $priority = 'medium';

    public function submit(): void
    {
        $this->validate();

        $ticket = app(OpenTicketAction::class)->handle(
            Auth::user(),
            $this->subject,
            $this->message,
            TicketPriority::from($this->priority),
        );

        $this->redirect(route('tickets.show', $ticket), navigate: true);
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <flux:button :href="route('tickets.index')" variant="ghost" icon="arrow-left" inset="left" wire:navigate />
        <div>
            <flux:heading size="xl">{{ __('Open a Support Ticket') }}</flux:heading>
            <flux:subheading>{{ __('Describe your issue and our team will get back to you.') }}</flux:subheading>
        </div>
    </div>

    <flux:card>
        <form wire:submit="submit" class="space-y-6">
            <flux:input
                wire:model="subject"
                :label="__('Subject')"
                placeholder="{{ __('Brief description of your issue') }}"
                required
            />

            <flux:select wire:model="priority" :label="__('Priority')">
                @foreach (\App\Enums\Tickets\TicketPriority::cases() as $p)
                    <flux:select.option :value="$p->value">{{ $p->getLabel() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:textarea
                wire:model="message"
                :label="__('Message')"
                placeholder="{{ __('Describe your issue in detail...') }}"
                rows="6"
                required
            />

            <div class="flex justify-end gap-2">
                <flux:button :href="route('tickets.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Submit Ticket') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
