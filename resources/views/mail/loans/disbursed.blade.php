<x-mail::message>
# Loan Disbursed

Hello {{ $user->name }},

Good news! Your loan of **${{ number_format($loan->amount, 2) }}** has been approved and disbursed to your wallet.

**Loan Details:**
- Amount: ${{ number_format($loan->amount, 2) }}
- Total Repayment: ${{ number_format($loan->total_repayment, 2) }}
- Next Payment Date: {{ $loan->next_payment_date->format('M d, Y') }}

Please ensure you have sufficient balance in your wallet for the monthly installments.

<x-mail::button :url="route('loans.details', $loan->id)">
View Loan Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
