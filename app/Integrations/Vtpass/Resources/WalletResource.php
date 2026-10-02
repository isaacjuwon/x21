<?php

declare(strict_types=1);

namespace App\Integrations\Vtpass\Resources;

use App\Enums\Http\Method;
use App\Integrations\Vtpass\Exceptions\VtpassException;
use App\Integrations\Vtpass\VtpassConnector;
use Throwable;

final readonly class WalletResource
{
    public function __construct(
        private VtpassConnector $connector,
    ) {}

    public function balance(): array
    {
        try {
            $response = $this->connector->send(
                method: Method::Get,
                uri: '/balance',
            );

            return $response->json() ?? [];
        } catch (Throwable $exception) {
            throw new VtpassException(
                message: 'Failed to fetch VTPass balance: '.$exception->getMessage(),
                previous: $exception,
            );
        }
    }
}
