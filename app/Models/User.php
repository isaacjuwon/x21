<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasLoans;
use App\Concerns\HasShares;
use App\Models\Concerns\IsVerified;
use App\Concerns\Wallet\ManagesWallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Concerns\HasReferrals;
use App\Models\Concerns\IsVerified;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Enums\Media\MediaCollectionType;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasLoans, HasReferrals, HasRoles, HasShares, IsVerified, ManagesWallet, Notifiable, TwoFactorAuthenticatable;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'loan_level_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionType::Avatar->value)
            ->singleFile();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl(MediaCollectionType::Avatar->value);
    }

    /**
     * Get the user's loan level
     */
    public function loanLevel()
    {
        return $this->belongsTo(LoanLevel::class);
    }

    /**
     * Get the user's tickets
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Boot the model
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            // Assign default 'user' role to newly created users
            if (!$user->referral_code) {
                $user->referral_code = static::generateReferralCode();
                $user->saveQuietly();
            }

            $user->assignRole('user');

            // Assign default 'Basic' loan level to newly created users
            if (!$user->loan_level_id) {
                $basicLevel = LoanLevel::where('slug', 'basic')->first();
                if ($basicLevel) {
                    $user->loan_level_id = $basicLevel->id;
                    $user->saveQuietly();
                }
            }
        });
    }
}
