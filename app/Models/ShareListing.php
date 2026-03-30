<?php

namespace App\Models;

use Database\Factories\ShareListingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareListing extends Model
{
    /** @use HasFactory<ShareListingFactory> */
    use HasFactory;

    protected $fillable = [
        'price',
        'total_shares',
        'available_shares',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }
}
