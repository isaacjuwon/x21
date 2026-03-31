<?php

namespace App\Models;

use App\Enums\Topups\TopupTransactionStatus;
use App\Enums\Topups\TopupType;
use Database\Factories\TopupTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopupTransaction extends Model
{
    /** @use HasFactory<TopupTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'plan_type',
        'brand_id',
        'type',
        'amount',
        'recipient',
        'reference',
        'api_reference',
        'status',
        'response_message',
        'meta',
    ];

    protected $casts = [
        'type' => TopupType::class,
        'status' => TopupTransactionStatus::class,
        'amount' => 'decimal:2',
        'meta' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function plan()
    {
        return $this->morphTo();
    }
}
