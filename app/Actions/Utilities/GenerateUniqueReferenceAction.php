<?php

namespace App\Actions\Utilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GenerateUniqueReferenceAction
{
    /**
     * Generate a unique reference for a given model and column.
     *
     * @param  class-string<Model>  $modelClass
     */
    public function handle(
        string $modelClass,
        string $prefix = 'REF',
        int $length = 10,
        string $column = 'reference'
    ): string {
        do {
            $reference = strtoupper($prefix . '-' . Str::random($length));
        } while ($modelClass::where($column, $reference)->exists());

        return $reference;
    }
}
