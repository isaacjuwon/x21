<x-mail::message>
# New Reply on Ticket #{{ $ticket->id }}

There is a new reply on your support ticket.

**Subject:** {{ $ticket->subject }}

**Message:**
{{ $message }}

<x-mail::button :url="$url">
View Conversation
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
