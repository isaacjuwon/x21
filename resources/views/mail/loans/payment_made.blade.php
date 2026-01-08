<x-mail::message>
# Payment Received

Hello {{ $user->name }},

We have received your payment of **${{ number_format($payment->amount, 2) }}** for Loan #{{ $loan->id }}.

**Payment Details:**
- Amount Paid: ${{ number_format($payment->amount, 2) }}
- Principal: ${{ number_format($payment->principal_amount, 2) }}
- Interest: ${{ number_format($payment->interest_amount, 2) }}
- Date: {{ $payment->payment_date->format('M d, Y') }}

**Loan Status:**
- Balance Remaining: ${{ number_format($loan->balance_remaining, 2) }}
@if($loan->next_payment_date)
- Next Payment Due: {{ $loan->next_payment_date->format('M d, Y') }}
@endif

<x-mail::button :url="route('loans.details', $loan->id)">
View Loan Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
