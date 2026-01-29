<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Payment\VerifyPaymentAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * Verify a payment reference.
     */
    public function verify(string $reference, VerifyPaymentAction $action): JsonResponse
    {
        $result = $action->handle($reference);

        if (!$result->success) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed.',
                'error' => $result->message,
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment verified.',
            'data' => $result->data,
        ]);
    }
}
