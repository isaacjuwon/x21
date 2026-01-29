<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Loan;

use App\Http\Payloads\Loan\ApplyLoanPayload;
use Illuminate\Foundation\Http\FormRequest;

class ApplyLoanRequest extends FormRequest
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

    public function payload(): ApplyLoanPayload
    {
        return new ApplyLoanPayload(
            amount: (float) $this->float('amount'),
        );
    }
}
