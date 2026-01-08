<x-mail::message>
# Shares Sold

Hello {{ $user->name }},

You have successfully sold **{{ $quantity }}** shares.

**Transaction Details:**
- Quantity: {{ $quantity }}
- Price per Share: ${{ number_format($price, 2) }}
- Total Amount: ${{ number_format($totalAmount, 2) }}

The amount has been credited to your wallet.

<x-mail::button :url="url('/')">
View Portfolio
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
