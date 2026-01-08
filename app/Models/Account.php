<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_name',
        'account_number',
        'bank_name',
        'bank_number',
        'user_id',
        'paystack_customer_code',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: User::class,
            foreignKey: 'user_id'
        );
    }

    public function getFormattedAccountNumberAttribute(): string
    {
        return chunk_split($this->account_number, 4, ' ');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->account_name . ' - ' . $this->bank_name;
    }
}
