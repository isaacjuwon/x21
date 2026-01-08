<?php

namespace App\Models\Builders;

use App\Enums\Transaction\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionBuilder extends Builder
{
   public function success(): TransactionBuilder
   {
        return $this->where(
            'status',
            '=',
            Status::Success
        );
   }

   public function type(Model $model): TransactionBuilder
   {
        return $this->whereHasMorph(
            'modelable',
            get_class($model),
            function (Builder $query) {
                //$query->where('title', 'like', 'code%');
            }
        );
   }
}
