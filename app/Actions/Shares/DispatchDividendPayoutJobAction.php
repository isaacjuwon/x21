<?php

namespace App\Actions\Shares;

use App\Enums\Shares\DividendStatus;
use App\Jobs\ProcessDividendPayoutsJob;
use App\Models\Dividend;
use Illuminate\Support\Facades\Cache;

class DispatchDividendPayoutJobAction
{
    /**
     * Dispatch the dividend payout job, guarded against duplicate execution.
     *
     * Returns true when the job was dispatched, false when it was skipped
     * because the dividend was already distributed or is currently being
     * processed by another request.
     */
    public function handle(Dividend $dividend): bool
    {
        // Fast path: already distributed — no need to acquire a lock.
        $dividend->refresh();

        if ($dividend->status === DividendStatus::Distributed) {
            return false;
        }

        $lock = Cache::lock("dividend-payout-dispatch:{$dividend->id}", 30);

        if (! $lock->get()) {
            // Another request is already dispatching this dividend.
            return false;
        }

        try {
            // Re-check inside the lock to close the TOCTOU window.
            $dividend->refresh();

            if ($dividend->status === DividendStatus::Distributed) {
                return false;
            }

            ProcessDividendPayoutsJob::dispatchSync($dividend);

            return true;
        } finally {
            $lock->release();
        }
    }
}
