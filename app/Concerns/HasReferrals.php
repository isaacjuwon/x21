<?php

namespace App\Concerns;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Str;

trait HasReferrals
{
    /**
     * Get the referrals made by this user.
     */
    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Get the referral record for this user (who referred them).
     */
    public function referralSource()
    {
        return $this->hasOne(Referral::class, 'referred_id');
    }

    /**
     * Get the user who referred this user.
     */
    public function referrer()
    {
        return $this->hasOneThrough(
            User::class,
            Referral::class,
            'referred_id', // Foreign key on referrals table...
            'id',          // Foreign key on users table...
            'id',          // Local key on users table...
            'referrer_id'  // Local key on referrals table...
        );
    }

    /**
     * Get the referral link.
     */
    public function getReferralLinkAttribute(): string
    {
        return route('register', ['ref' => $this->referral_code]);
    }

    /**
     * Generate a unique referral code.
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }
}
