<?php

namespace App\Concerns;

use App\Models\KycVerification;
use App\Enums\Kyc\Status as KycStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait IsVerified
{
    /**
     * Get all KYC verifications for this user
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(KycVerification::class, 'user_id');
    }

    /**
     * Check if user is KYC verified
     */
    public function isVerified(): bool
    {
        return (bool) $this->is_verified;
    }

    /**
     * Mark user as verified
     */
    public function markAsVerified(): self
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark user as not verified
     */
    public function markAsNotVerified(): self
    {
        $this->update([
            'is_verified' => false,
            'verified_at' => null,
        ]);

        return $this;
    }

    /**
     * Check if user has at least one verified KYC record
     */
    public function hasVerifiedKyc(): bool
    {
        return $this->verifications()
            ->where('status', KycStatusEnum::Verified)
            ->exists();
    }

    /**
     * Get the latest verified KYC record
     */
    public function getVerifiedKyc(): ?KycVerification
    {
        return $this->verifications()
            ->where('status', KycStatusEnum::Verified)
            ->latest()
            ->first();
    }
}
