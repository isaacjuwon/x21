<?php

namespace App\Models;

use App\Enums\Shares\DividendStatus;
use Database\Factories\DividendFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dividend extends Model
{
    /** @use HasFactory<DividendFactory> */
    use HasFactory;

    protected $fillable = [
        'total_amount',
        'status',
        'declared_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DividendStatus::class,
            'declared_at' => 'datetime',
        ];
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(DividendPayout::class);
    }
}
