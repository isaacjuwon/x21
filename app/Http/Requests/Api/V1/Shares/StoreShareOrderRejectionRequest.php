<?php

namespace App\Http\Requests\Api\V1\Shares;

use Illuminate\Foundation\Http\FormRequest;

class StoreShareOrderRejectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string'],
        ];
    }
}
