<?php

declare(strict_types=1);

namespace App\Integrations\Vtugate\Resources;

use App\Enums\Http\Method;
use App\Integrations\Vtugate\Exceptions\VtugateException;
use App\Integrations\Vtugate\VtugateConnector;
use Throwable;

final readonly class WalletResource
{
    public function __construct(
        private VtugateConnector $connector,
    ) {}

    public function balance(): array
    {
        try {
            $response = $this->connector->send(
                method: Method::Post,
                uri: '/api/v1/accountdetails',
                options: [],
            );

            return $response->json() ?? [];
        } catch (Throwable $exception) {
            throw new VtugateException(
                message: 'Failed to fetch VtuGate balance: '.$exception->getMessage(),
                previous: $exception,
            );
        }
    }
}
