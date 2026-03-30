<?php

namespace App\Actions\Shares;

use App\Enums\Shares\DividendStatus;
use App\Events\Shares\DividendDeclared;
use App\Jobs\ProcessDividendPayoutsJob;
use App\Models\Dividend;

class DeclareDividendAction
{
    public function handle(float $amount): Dividend
    {
        $dividend = Dividend::create([
            'total_amount' => $amount,
            'status' => DividendStatus::Pending,
            'declared_at' => now(),
        ]);

        DividendDeclared::dispatch($dividend);

        ProcessDividendPayoutsJob::dispatch($dividend);

        return $dividend;
    }
}
