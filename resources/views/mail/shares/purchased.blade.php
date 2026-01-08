<x-mail::message>
# Shares Purchased

Hello {{ $user->name }},

You have successfully purchased **{{ $quantity }}** shares.

**Transaction Details:**
- Quantity: {{ $quantity }}
- Price per Share: ${{ number_format($price, 2) }}
- Total Amount: ${{ number_format($totalAmount, 2) }}

Your portfolio has been updated.

<x-mail::button :url="url('/')">
View Portfolio
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
