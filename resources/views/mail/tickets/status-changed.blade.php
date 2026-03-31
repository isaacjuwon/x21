<x-mail::message>
# Ticket Status Updated

Hi {{ $notifiable->name }},

Your ticket status has been updated.

<x-mail::panel>
**Ticket ID:** #{{ $ticket->id }}
**Subject:** {{ $ticket->subject }}
**New Status:** {{ $ticket->status->getLabel() }}
@if($ticket->resolved_at)
**Resolved At:** {{ $ticket->resolved_at->format('M j, Y H:i') }}
@endif
</x-mail::panel>

<x-mail::button :url="route('tickets.show', $ticket)">
View Ticket
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
