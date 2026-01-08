<x-mail::message>
# Wallet Credited

Hello {{ $user->name }},

Your {{ $type->getLabel() }} has been credited with **${{ number_format($amount, 2) }}**.

**Details:**
- Amount: ${{ number_format($amount, 2) }}
- Wallet: {{ $type->getLabel() }}
@if($notes)
- Notes: {{ $notes }}
@endif

<x-mail::button :url="url('/')">
View Wallet
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
