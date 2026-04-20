<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasWallets;
use App\Enums\Kyc\KycType;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'avatar', 'phone_number', 'api_token'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token', 'api_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasWallets, Notifiable, TwoFactorAuthenticatable;

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole('super_admin') || $this->hasPermissionTo('view_admin_panel');
        }

        return true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::created(function (User $user) {
            $basicLevel = LoanLevel::where('name', 'Basic')->first();
            if ($basicLevel) {
                $user->loanLevel()->associate($basicLevel)->save();
            }
        });
    }

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

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, Wallet::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function loanLevel(): BelongsTo
    {
        return $this->belongsTo(LoanLevel::class);
    }

    public function shareHolding(): HasOne
    {
        return $this->hasOne(ShareHolding::class);
    }

    public function shareOrders(): HasMany
    {
        return $this->hasMany(ShareOrder::class);
    }

    public function dividendPayouts(): HasMany
    {
        return $this->hasMany(DividendPayout::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function kycs(): HasMany
    {
        return $this->hasMany(Kyc::class);
    }

    public function getKyc(KycType $type): ?Kyc
    {
        return $this->kycs()->where('type', $type)->first();
    }

    public function isKycVerified(?KycType $type = null): bool
    {
        if ($type) {
            return $this->getKyc($type)?->isVerified() ?? false;
        }

        // Global check: user is considered verified if they have BOTH NIN and BVN verification
        return $this->isKycVerified(KycType::Nin) && $this->isKycVerified(KycType::Bvn);
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
}
