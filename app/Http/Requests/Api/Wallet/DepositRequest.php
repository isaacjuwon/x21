<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Wallet;

use App\Http\Payloads\Wallet\DepositPayload;
use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
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

    public function payload(): DepositPayload
    {
        return new DepositPayload(
            amount: (float) $this->float('amount'),
        );
    }
}
