<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Wallet;

use App\Http\Payloads\Wallet\TransferPayload;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'phone_number' => ['required', 'string', 'exists:users,phone_number'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function payload(): TransferPayload
    {
        return new TransferPayload(
            amount: (float) $this->float('amount'),
            phoneNumber: $this->string('phone_number')->toString(),
            notes: $this->filled('notes') ? $this->string('notes')->toString() : null,
        );
    }
}
