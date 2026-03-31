<x-mail::message>
# Wallet Refund Processed

Hi {{ $notifiable->name }},

A refund has been credited to your wallet for a failed transaction.

<x-mail::panel>
**Refunded Amount:** {{ Number::currency($original->amount) }}
**Original Reference:** {{ $original->reference }}
**Refund Reference:** {{ $refund->reference }}
@if($original->failure_reason)
**Reason:** {{ $original->failure_reason }}
@endif
</x-mail::panel>

<x-mail::button :url="route('wallet.transactions')">
View Transactions
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
