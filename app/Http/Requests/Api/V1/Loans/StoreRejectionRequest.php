<?php

namespace App\Http\Requests\Api\V1\Loans;

use Illuminate\Foundation\Http\FormRequest;

class StoreRejectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function bodyParameters(): array { return []; }
}
