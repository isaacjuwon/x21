<x-mail::message>
# Loan Fully Paid

Hello {{ $user->name }},

Congratulations! You have fully repaid your loan of **${{ number_format($loan->amount, 2) }}**.

Thank you for being a valued member. You are now eligible to apply for a new loan if needed.

<x-mail::button :url="route('loans.index')">
View Loans
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
