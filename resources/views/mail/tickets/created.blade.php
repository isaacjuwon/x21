<x-mail::message>
# New Ticket: #{{ $ticket->id }}

A new support ticket has been created by **{{ $ticket->user->name }}**.

**Subject:** {{ $ticket->subject }}
**Priority:** {{ ucfirst($ticket->priority) }}

<x-mail::button :url="$url">
View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
