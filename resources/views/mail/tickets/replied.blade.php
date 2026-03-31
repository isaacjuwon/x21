<x-mail::message>
# New Reply on Your Ticket

Hi {{ $notifiable->name }},

{{ $reply->is_staff ? 'A support agent has replied to your ticket.' : 'The user has replied to the ticket.' }}

<x-mail::panel>
**Ticket ID:** #{{ $reply->ticket_id }}
**Reply from:** {{ $reply->user->name }}

{{ $reply->message }}
</x-mail::panel>

<x-mail::button :url="route('tickets.show', $reply->ticket_id)">
View Ticket
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
