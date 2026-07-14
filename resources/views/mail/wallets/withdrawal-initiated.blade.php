<x-mail::message>
# Withdrawal Initiated

Hi {{ $notifiable->name }},

Your withdrawal request has been initiated and is being processed.

<x-mail::panel>
**Amount:** {{ Number::currency($transaction->meta['amount'] ?? $transaction->amount) }}
@if(($transaction->meta['fee'] ?? 0) > 0)
**Processing Fee:** {{ Number::currency($transaction->meta['fee']) }}
@endif
@if(($transaction->meta['stamp_duty'] ?? 0) > 0)
**Stamp Duty:** {{ Number::currency($transaction->meta['stamp_duty']) }}
@endif
**Total Deducted:** {{ Number::currency($transaction->amount) }}
**Account Name:** {{ $transaction->meta['account_name'] ?? '—' }}
**Bank:** {{ $transaction->meta['bank_name'] ?? '—' }}
**Account Number:** {{ $transaction->meta['account_number'] ?? '—' }}
**Reference:** {{ $transaction->reference }}
</x-mail::panel>

Funds are typically delivered within a few minutes. If you did not initiate this withdrawal, please contact support immediately.

<x-mail::button :url="route('wallet.transactions')">
View Transaction History
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
