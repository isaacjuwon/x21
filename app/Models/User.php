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
use Illuminate\Database\Eloquent\Casts\Attribute;
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

#[Fillable(['name', 'email', 'password', 'avatar', 'phone_number'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
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

    public function shareHoldings(): HasMany
    {
        return $this->hasMany(ShareHolding::class);
    }

    public function getTotalSharesAttribute(): int
    {
        return $this->shareHoldings()->sum('quantity');
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

    /**
     * Get the user's first name.
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $words = explode(' ', trim($this->name));

                return $words[0] ?? '';
            },
        );
    }

    /**
     * Get the user's middle name.
     */
    protected function middleName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $words = explode(' ', trim($this->name));
                if (count($words) > 2) {
                    return implode(' ', array_slice($words, 1, -1));
                }

                return '';
            },
        );
    }

    /**
     * Get the user's last name.
     */
    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $words = explode(' ', trim($this->name));
                if (count($words) > 1) {
                    return end($words);
                }

                return '';
            },
        );
    }
}
