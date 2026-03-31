<?php

namespace App\Http\Requests\Api\V1\Loans;

use Illuminate\Foundation\Http\FormRequest;

class StoreRepaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $loan = $this->route('loan');
        $max = $loan ? (float) $loan->outstanding_balance : 999999;

        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:'.$max,
            ],
        ];
    }
}
