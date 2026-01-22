<?php

declare(strict_types=1);

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\Kyc\Type as KycType;
use App\Enums\Kyc\Status as KycStatusEnum;
use App\Enums\Kyc\VerificationMode;

class KycVerification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type', // 'bvn' or 'nin'
        'id_number',
        'dob',
        'phone',
        'email',
        'status', // 'pending', 'verified', 'failed'
        'verification_mode', // 'automatic' or 'manual'
        'document_path', // Path to uploaded document
        'response', // JSON response from Dojah
        'meta', // Any extra info
    ];


    protected $casts = [
        'response' => 'array',
        'meta' => 'array',
        'type' => KycType::class,
        'status' => KycStatusEnum::class,
        'verification_mode' => VerificationMode::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
