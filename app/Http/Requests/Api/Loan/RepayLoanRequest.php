<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Loan;

use App\Http\Payloads\Loan\RepayLoanPayload;
use Illuminate\Foundation\Http\FormRequest;

class RepayLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function payload(): RepayLoanPayload
    {
        return new RepayLoanPayload(
            amount: (float) $this->float('amount'),
        );
    }
}
