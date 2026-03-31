<?php

namespace App\Http\Requests\Api\V1\Loans;

use App\Actions\Loans\CheckLoanEligibilityAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'principal_amount' => ['required', 'numeric', 'min:1'],
            'repayment_term_months' => ['required', 'integer', 'min:1'],
            'interest_method' => ['sometimes', 'string', 'in:FlatRate,ReducingBalance'],
        ];
    }

    public function after(): array
    {
        return [
            function () {
                if ($this->isNotFilled('principal_amount') || ! is_numeric($this->principal_amount)) {
                    return;
                }

                $result = app(CheckLoanEligibilityAction::class)->handle(
                    $this->user(),
                    (float) $this->principal_amount
                );

                if (! $result->passed) {
                    throw ValidationException::withMessages([
                        'principal_amount' => $result->failingSpecification?->failureReason() ?? 'You are not eligible for this loan.',
                    ]);
                }
            },
        ];
    }
}
