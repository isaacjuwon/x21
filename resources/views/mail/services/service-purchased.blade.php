<x-mail::message>
# {{ $transaction->type->getLabel() }} Purchase Successful

Hi {{ $notifiable->name }},

Your {{ $transaction->type->getLabel() }} purchase was completed successfully.

<x-mail::panel>
**Service:** {{ $transaction->type->getLabel() }}
**Recipient:** {{ $transaction->recipient }}
**Amount:** {{ Number::currency($transaction->amount) }}
**Reference:** {{ $transaction->reference }}
@if($transaction->api_reference)
**Provider Ref:** {{ $transaction->api_reference }}
@endif
</x-mail::panel>

The service has been delivered to **{{ $transaction->recipient }}**.

<x-mail::button :url="route('wallet.transactions')">
View Transaction History
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
