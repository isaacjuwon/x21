<x-mail::message>
# Loan Approved

Hi {{ $notifiable->name }},

Your loan application has been **approved** and is now active.

<x-mail::panel>
**Loan ID:** #{{ $loan->id }}
**Principal:** {{ Number::currency($loan->principal_amount) }}
**Interest Rate:** {{ $loan->interest_rate }}%
**Term:** {{ $loan->repayment_term_months }} months
</x-mail::panel>

You will be notified once your loan is disbursed.

<x-mail::button :url="route('loan.view', $loan)">
View Loan
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
