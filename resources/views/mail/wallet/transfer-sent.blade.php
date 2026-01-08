<x-mail::message>
# Transfer Sent

Hello {{ $sender->name }},

You have successfully transferred **₦{{ number_format($amount, 2) }}** to **{{ $recipient->name }}**.

**Transfer Details:**
- Amount: ₦{{ number_format($amount, 2) }}
- Recipient: {{ $recipient->name }}
- Phone: {{ $recipient->phone_number }}
- Wallet: {{ $type->getLabel() }}
@if($notes)
- Notes: {{ $notes }}
@endif

<x-mail::button :url="route('wallet.index')">
View Wallet
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
