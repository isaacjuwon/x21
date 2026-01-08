<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(?string $conversion = null): string
    {
        $path = $this->path;

        if ($conversion && isset($this->meta['conversions'][$conversion])) {
            $path = $this->meta['conversions'][$conversion];
        }

        return Storage::disk($this->disk)->url($path);
    }
}
