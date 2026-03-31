<x-mail::message>
# Ticket Received

Hi {{ $notifiable->name }},

Your support ticket has been received. Our team will respond shortly.

<x-mail::panel>
**Ticket ID:** #{{ $ticket->id }}
**Subject:** {{ $ticket->subject }}
**Priority:** {{ $ticket->priority->getLabel() }}
**Status:** {{ $ticket->status->getLabel() }}
</x-mail::panel>

<x-mail::button :url="route('tickets.show', $ticket)">
View Ticket
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
