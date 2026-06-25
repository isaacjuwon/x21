<?php

namespace App\Actions\Shares;

use App\Jobs\ProcessDividendPayoutsJob;
use App\Models\Dividend;

class DispatchDividendPayoutJobAction
{
    public function handle(Dividend $dividend): void
    {
        ProcessDividendPayoutsJob::dispatchSync($dividend);
    }
}
