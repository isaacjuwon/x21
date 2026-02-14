<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Payment\VerifyPaymentAction;
use Illuminate\Http\JsonResponse;

class PaymentController extends ApiController
{
    /**
     * Verify a payment reference.
     */
    public function verify(string $reference, VerifyPaymentAction $action): JsonResponse
    {
        $result = $action->handle($reference);

        if (! $result->success) {
            return $this->errorResponse($result->message, 400);
        }

        return $this->successResponse($result->data, 'Payment verified.');
    }
}
