<x-mail::message>
# Loan Application Received

Hello {{ $user->name }},

We have received your loan application for **${{ number_format($loan->amount, 2) }}**.

**Loan Details:**
- Amount: ${{ number_format($loan->amount, 2) }}
- Interest Rate: {{ $loan->interest_rate }}%
- Installment Period: {{ $loan->installment_months }} months
- Monthly Payment: ${{ number_format($loan->monthly_payment, 2) }}

Your application is currently **{{ $loan->status->getLabel() }}**.

<x-mail::button :url="route('loans.details', $loan->id)">
View Loan Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
