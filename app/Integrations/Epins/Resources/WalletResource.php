<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Enums\Http\Method;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\Exceptions\EpinsException;
use Throwable;

final readonly class WalletResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    public function balance(): array
    {
        try {
            $response = $this->connector->send(
                method: Method::GET,
                uri: '/account/',
            );
        } catch (Throwable $exception) {
            throw new EpinsException(
                message: 'Failed to get wallet balance: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        return $response->json('description');
    }
}
