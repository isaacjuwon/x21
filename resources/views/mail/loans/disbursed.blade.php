<x-mail::message>
# Loan Disbursed

Hi {{ $notifiable->name }},

Your loan has been **disbursed** to your wallet.

<x-mail::panel>
**Loan ID:** #{{ $loan->id }}
**Amount Disbursed:** {{ Number::currency($loan->principal_amount) }}
**Outstanding Balance:** {{ Number::currency($loan->outstanding_balance) }}
</x-mail::panel>

Your repayment schedule is now active. Please ensure timely payments to avoid penalties.

<x-mail::button :url="route('loan.view', $loan)">
View Repayment Schedule
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
