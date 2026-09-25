<?php

namespace App\Http\Requests\Api\V1\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
       return [
        'phone_number' => [
            'required',
            'exists:users,phone_number',
            Rule::notIn([$this->user()->phone_number]),
        ],
        'amount' => ['required', 'numeric', 'min:1'],
        'notes' => ['nullable', 'string', 'max:255'],
    ];
    }
}
