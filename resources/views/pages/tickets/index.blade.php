<?php

use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Tickets'), Defer] class extends Component {
    use WithPagination;

    #[Url]
    public string $filterStatus = '';

    public function updatedFilterStatus(): void { $this->resetPage(); }

    #[Computed]
    public function tickets()
    {
        return Auth::user()->tickets()
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->with('replies')
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function openCount(): int
    {
        return Auth::user()->tickets()->open()->count();
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="space-y-6 animate-pulse">
            <div class="flex items-center justify-between">
                <div class="h-8 w-32 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
                <div class="h-10 w-36 bg-zinc-200 dark:bg-zinc-700 rounded"></div>
            </div>
            <div class="h-64 bg-zinc-200 dark:bg-zinc-700 rounded-xl"></div>
        </div>
        HTML;
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Support Tickets') }}</flux:heading>
            <flux:subheading>{{ __('Track and manage your support requests.') }}</flux:subheading>
        </div>
        <flux:button href="{{ route('tickets.create') }}" variant="primary" icon="plus" wire:navigate>
            {{ __('New Ticket') }}
        </flux:button>
    </div>

    @if($this->openCount > 0)
        <flux:callout icon="information-circle" color="blue" variant="secondary">
            <flux:callout.text>You have {{ $this->openCount }} open {{ str('ticket')->plural($this->openCount) }}.</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Filter --}}
    <div class="flex gap-3">
        <flux:select wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}" class="w-44">
            <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
            @foreach (\App\Enums\Tickets\TicketStatus::cases() as $status)
                <flux:select.option :value="$status->value">{{ $status->getLabel() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:card class="p-2 overflow-hidden border-zinc-200 dark:border-zinc-800">
        <flux:table :paginate="$this->tickets">
            <flux:table.columns>
                <flux:table.column>{{ __('#') }}</flux:table.column>
                <flux:table.column>{{ __('Subject') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Priority') }}</flux:table.column>
                <flux:table.column>{{ __('Replies') }}</flux:table.column>
                <flux:table.column>{{ __('Opened') }}</flux:table.column>
                <flux:table.column align="end"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->tickets as $ticket)
                    <flux:table.row :key="$ticket->id">
                        <flux:table.cell class="text-zinc-400 text-sm font-mono">
                            #{{ $ticket->id }}
                        </flux:table.cell>

                        <flux:table.cell class="font-medium max-w-xs truncate">
                            {{ $ticket->subject }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge :color="$ticket->status->getFluxColor()" :icon="$ticket->status->getFluxIcon()" size="sm" inset="top bottom">
                                {{ $ticket->status->getLabel() }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge :color="$ticket->priority->getFluxColor()" size="sm" inset="top bottom">
                                {{ $ticket->priority->getLabel() }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500 text-sm">
                            {{ $ticket->replies->count() }}
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500 text-sm whitespace-nowrap">
                            {{ $ticket->created_at->diffForHumans() }}
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            <flux:button :href="route('tickets.show', $ticket)" size="sm" variant="ghost" icon="eye" inset="top bottom" wire:navigate />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="py-12 text-center text-zinc-400">
                            {{ __('No tickets found. Open one if you need help.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
