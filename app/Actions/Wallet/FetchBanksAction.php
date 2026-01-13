<?php

declare(strict_types=1);

namespace App\Actions\Wallet;

use App\Enums\Connectors\PaymentConnector;
use Illuminate\Support\Collection;
use Throwable;

final class FetchBanksAction
{
    /**
     * @return Collection<int, \App\Integrations\Paystack\Entities\Bank>
     */
    public function handle(string $country = 'nigeria'): Collection
    {
        try {
            return PaymentConnector::default()
                ->connector()
                ->bank()
                ->list(country: $country);
        } catch (Throwable) {
            return collect();
        }
    }
}
