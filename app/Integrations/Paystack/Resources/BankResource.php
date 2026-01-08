<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Resources;

use App\Enums\Http\Method;
use App\Integrations\Paystack\Entities\Bank;
use App\Integrations\Paystack\Entities\BankAccount;
use App\Integrations\Paystack\Exceptions\PaystackException;
use App\Integrations\Paystack\PaystackConnector;
use Illuminate\Support\Collection;
use Throwable;

final readonly class BankResource
{
    public function __construct(
        private PaystackConnector $connector,
    ) {}

    /**
     * @return Collection<int, Bank>
     */
    public function list(string $country = 'nigeria'): Collection
    {
        try {
            $response = $this->connector->send(
                method: Method::GET,
                uri: '/bank',
                options: ['country' => $country],
            );

            return collect($response->json('data'))
                ->map(fn (array $bank) => Bank::fromArray($bank));
        } catch (Throwable $exception) {
            throw new PaystackException(
                message: 'Failed to fetch banks: ' . $exception->getMessage(),
                previous: $exception,
            );
        }
    }

    public function resolve(string $accountNumber, string $bankCode): BankAccount
    {
        try {
            $response = $this->connector->send(
                method: Method::GET,
                uri: '/bank/resolve',
                options: [
                    'account_number' => $accountNumber,
                    'bank_code' => $bankCode,
                ],
            );

            return BankAccount::fromArray($response->json('data'));
        } catch (Throwable $exception) {
            throw new PaystackException(
                message: 'Failed to resolve account: ' . $exception->getMessage(),
                previous: $exception,
            );
        }
    }
}
