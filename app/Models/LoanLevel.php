<?php

namespace App\Models;

use Database\Factories\LoanLevelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanLevel extends Model
{
    /** @use HasFactory<LoanLevelFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'max_amount',
        'min_amount',
        'interest_rate',
        'max_term_months',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_amount' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
