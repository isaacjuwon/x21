<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Loans\CheckLoanEligibilityAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanEligibilityController extends Controller
{
    public function store(Request $request, CheckLoanEligibilityAction $action): JsonResponse
    {
        $request->validate([
            'principal_amount' => ['required', 'numeric', 'min:1'],
        ]);

        $result = $action->handle($request->user(), (float) $request->principal_amount);

        return response()->json([
            'passed' => $result->passed,
            'reason' => null,
        ]);
    }
}
