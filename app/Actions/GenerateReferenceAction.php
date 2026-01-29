<?php

namespace App\Actions;

use Illuminate\Support\Str;

class GenerateReferenceAction
{
    public function handle(string $prefix = 'TRX'): string
    {
        return $prefix.'-'.strtoupper(Str::random(20));
    }
}
