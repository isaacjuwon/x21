<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Actions\Tickets\CreateTicket;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    #[Validate('required|min:5|max:255')]
    public string $subject = '';

    #[Validate('required|in:low,medium,high')]
    public string $priority = 'medium';

    #[Validate('required|min:10')]
    public string $message = '';

    public function save(CreateTicket $action)
    {
        $this->validate();

        $ticket = $action->execute(
            Auth::user(),
            $this->subject,
            $this->message,
            $this->priority
        );

        $this->redirect(route('tickets.show', $ticket), navigate: true);
    }

    public function render()
    {
        return $this->view()
            ->title('Create Ticket')
            ->layout('layouts::app');
    }
};
?>

<div class="max-w-2xl mx-auto p-6">
    <x-page-header 
        heading="Create Ticket" 
        description="Submit a new support request"
        back-url="{{ route('tickets.index') }}"
        back-label="Back to Tickets"
    />

    <div class="mt-6">
        <x-ui.card>
            <form wire:submit="save" class="space-y-6">
                <x-ui.field>
                    <x-ui.label for="subject" class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Subject</x-ui.label>
                    <x-ui.input wire:model="subject" id="subject" placeholder="Briefly describe your issue" class="text-base font-bold tracking-widest h-14" />
                    <x-ui.error name="subject" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="priority" class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Priority</x-ui.label>
                    <x-ui.select wire:model="priority" id="priority" class="h-14 font-bold tracking-widest">
                        <x-ui.select.option value="low">Low - General Question</x-ui.select.option>
                        <x-ui.select.option value="medium">Medium - Issue needing attention</x-ui.select.option>
                        <x-ui.select.option value="high">High - Critical Issue</x-ui.select.option>
                    </x-ui.select>
                    <x-ui.error name="priority" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="message" class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Message</x-ui.label>
                    <x-ui.textarea wire:model="message" id="message" rows="5" placeholder="Describe the issue in detail..." class="font-bold tracking-widest pt-4" />
                    <x-ui.error name="message" />
                </x-ui.field>

                <div class="flex flex-col-reverse md:flex-row justify-end gap-3 pt-4">
                    <x-ui.button type="button" variant="ghost" wire:navigate href="{{ route('tickets.index') }}" class="h-14 px-8 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs">Cancel</x-ui.button>
                    <x-ui.button type="submit" class="h-14 px-12 rounded-[--radius-box] font-bold uppercase tracking-widest text-xs shadow-lg shadow-primary/20">Submit Ticket</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>
