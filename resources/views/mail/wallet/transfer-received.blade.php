<x-mail::message>
# Transfer Received

Hello {{ $recipient->name }},

You have received **₦{{ number_format($amount, 2) }}** from **{{ $sender->name }}**.

**Transfer Details:**
- Amount: ₦{{ number_format($amount, 2) }}
- From: {{ $sender->name }}
- Phone: {{ $sender->phone_number }}
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
