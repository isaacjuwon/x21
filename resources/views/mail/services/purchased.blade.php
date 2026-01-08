<x-mail::message>
# Service Purchase Confirmation

Hello {{ $user->name }},

Your **{{ ucfirst($serviceType) }}** purchase has been completed successfully!

**Transaction Details:**
- Product: {{ $productName }}
- Amount: ${{ number_format($amount, 2) }}
- Reference: {{ $transactionReference }}

@if($transactionUrl)
<x-mail::button :url="$transactionUrl">
View Transaction
</x-mail::button>
@else
<x-mail::button :url="url('/dashboard')">
View Dashboard
</x-mail::button>
@endif

Thank you for using our service.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
