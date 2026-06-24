<?php

declare(strict_types=1);

namespace App\Integrations\Dojah\Resources;

use App\Integrations\Dojah\DojahConnector;
use App\Integrations\Dojah\Entities\BvnMatchRequest;
use App\Integrations\Dojah\Entities\BvnSelfieRequest;
use App\Integrations\Dojah\Entities\NinLookupRequest;
use App\Integrations\Dojah\Entities\NinSelfieRequest;
use App\Integrations\Dojah\Entities\VerificationRequest;
use App\Integrations\Dojah\Entities\VerificationResponse;
use App\Integrations\Dojah\Exceptions\DojahException;
use Throwable;

final readonly class VerificationResource
{
    public function __construct(
        private DojahConnector $connector,
    ) {}

    public function verify(VerificationRequest $entity): VerificationResponse
    {
        try {
            $response = $this->connector->send(
                method: 'post',
                uri: '/api/v1/kyc/verify',
                options: ['json' => $entity->toRequestBody()],
            );
        } catch (Throwable $exception) {
            throw new DojahException(
                message: 'Failed to verify identity: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        return VerificationResponse::fromResponse($response->json());
    }

    public function bvnMatch(BvnMatchRequest $request): VerificationResponse
    {
        try {
            $response = $this->connector->send(
                method: 'get',
                uri: '/api/v1/kyc/bvn',
                options: ['query' => $request->toQuery()],
            );

            $data = $response->json();
            $entity = $data['entity'] ?? null;
            $success = ! empty($entity);

            return new VerificationResponse(
                success: $success,
                data: $data,
                message: $data['error'] ?? $data['message'] ?? ($success ? 'BVN Lookup completed' : 'BVN Lookup failed')
            );
        } catch (Throwable $exception) {
            throw new DojahException(
                message: 'Failed BVN match verification: '.$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    public function bvnWithSelfie(BvnSelfieRequest $request): VerificationResponse
    {
        try {
            $response = $this->connector->send(
                method: 'post',
                uri: '/api/v1/kyc/bvn/verify',
                options: ['json' => $request->toRequestBody()],
            );
        } catch (Throwable $exception) {
            throw new DojahException(
                message: 'Failed BVN with selfie verification: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        return VerificationResponse::fromResponse($response->json());
    }

    public function ninLookup(NinLookupRequest $request): VerificationResponse
    {
        try {
            $response = $this->connector->send(
                method: 'get',
                uri: '/api/v1/kyc/nin',
                options: ['query' => $request->toQuery()],
            );

            $data = $response->json();
            $entity = $data['entity'] ?? null;
            $success = ! empty($entity);

            return new VerificationResponse(
                success: $success,
                data: $entity,
                message: $data['error'] ?? $data['message'] ?? ($success ? 'NIN Lookup completed' : 'NIN Lookup failed')
            );
        } catch (Throwable $exception) {
            throw new DojahException(
                message: 'Failed NIN lookup verification: '.$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    public function ninWithSelfie(NinSelfieRequest $request): VerificationResponse
    {
        try {
            $response = $this->connector->send(
                method: 'post',
                uri: '/api/v1/kyc/nin/verify',
                options: ['json' => $request->toRequestBody()],
            );
        } catch (Throwable $exception) {
            throw new DojahException(
                message: 'Failed NIN with selfie verification: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        return VerificationResponse::fromResponse($response->json());
    }
}
