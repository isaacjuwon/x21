<?php

namespace App\Actions\Shares;

use App\Enums\Shares\DividendStatus;
use App\Events\Shares\DividendDeclared;
use App\Jobs\ProcessDividendPayoutsJob;
use App\Models\Dividend;
use App\Settings\ShareSettings;

class DeclareDividendAction
{
    public function handle(float $percentage): Dividend
    {
        $settings = app(ShareSettings::class);

        $dividend = Dividend::create([
            'percentage' => $percentage,
            'share_price' => $settings->price_per_share,
            'status' => DividendStatus::Pending,
            'declared_at' => now(),
        ]);

        DividendDeclared::dispatch($dividend);

        ProcessDividendPayoutsJob::dispatch($dividend);

        return $dividend;
    }
}
