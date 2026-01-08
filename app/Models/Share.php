<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class Share extends Model
{
    protected $fillable = ["holder_type", "holder_id", "currency", "quantity", "status", "approved_at"];

    protected $casts = [
        "quantity" => "integer",
        "status" => \App\Enums\ShareStatus::class,
        "approved_at" => "datetime",
    ];

    public function holder(): MorphTo
    {
        return $this->morphTo();
    }
}
