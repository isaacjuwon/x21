<x-mail::message>
# Loan Application Rejected

Hi {{ $notifiable->name }},

Unfortunately, your loan application has been **rejected**.

<x-mail::panel>
**Loan ID:** #{{ $loan->id }}
**Principal Requested:** {{ Number::currency($loan->principal_amount) }}
@if($loan->rejection_reason)
**Reason:** {{ $loan->rejection_reason }}
@endif
</x-mail::panel>

If you have questions, please contact our support team.

<x-mail::button :url="route('loan.index')" color="red">
View My Loans
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
