@props([
    'type' => null, // KycType or null for global
    'user' => auth()->user(),
])

@php
    use App\Enums\Kyc\KycType;
    
    $kycType = $type instanceof KycType ? $type : ($type ? KycType::from($type) : null);
    $isVerified = $user?->isKycVerified($kycType);
    $kycRecord = $kycType ? $user?->getKyc($kycType) : null;
    
    $color = $isVerified ? 'green' : ($kycRecord?->status->getColor() ?? 'zinc');
    $label = $isVerified ? __('Verified') : ($kycRecord?->status->getLabel() ?? __('Unverified'));
    $icon = $isVerified ? 'check-circle' : ($kycRecord?->status === \App\Enums\Kyc\KycStatus::Pending ? 'clock' : 'x-circle');
@endphp

<flux:badge :color="$color" :icon="$icon" {{ $attributes }}>
    {{ $label }}
</flux:badge>
