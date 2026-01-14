<x-mail::message>
# KYC Verification Approved ✓

Hello {{ $notifiable->name }}!

Great news! Your KYC verification has been successfully approved.

<x-mail::panel>
**Verification Type:** {{ strtoupper($kycVerification->type) }}

**Status:** Verified
</x-mail::panel>

You can now access all features that require identity verification.

<x-mail::button :url="route('dashboard')">
Visit Dashboard
</x-mail::button>

Thank you for completing your KYC verification!

Best regards,<br>
{{ config('app.name') }}
</x-mail::message>
