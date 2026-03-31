<x-mail::message>
# Loan Fully Settled 🎉

Hi {{ $notifiable->name }},

Congratulations! Your loan has been **fully settled**.

<x-mail::panel>
**Loan ID:** #{{ $loan->id }}
**Principal Amount:** {{ Number::currency($loan->principal_amount) }}
</x-mail::panel>

Thank you for your timely repayments. You are eligible to apply for a new loan.

<x-mail::button :url="route('loan.index')">
View My Loans
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
