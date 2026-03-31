<x-mail::message>
# Dividend Payment Received

Hi {{ $notifiable->name }},

A dividend payment has been credited to your wallet.

<x-mail::panel>
**Amount:** {{ Number::currency($payout->amount) }}
**Declared At:** {{ $payout->dividend->declared_at->format('M j, Y') }}
</x-mail::panel>

The funds are now available in your general wallet.

<x-mail::button :url="route('wallet.index')">
View Wallet
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
