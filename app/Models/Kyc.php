<?php

namespace App\Models;

use App\Enums\Kyc\KycMethod;
use App\Enums\Kyc\KycStatus;
use App\Enums\Kyc\KycType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kyc extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'number',
        'method',
        'status',
        'data',
        'file_path',
        'rejection_reason',
        'verified_at',
    ];

    protected $casts = [
        'type' => KycType::class,
        'method' => KycMethod::class,
        'status' => KycStatus::class,
        'data' => 'array',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerified(): bool
    {
        return $this->status === KycStatus::Verified;
    }
}
