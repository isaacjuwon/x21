<?php

namespace App\Http\Requests\Api\V1\Services;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseAirtimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'phone_number' => ['required', 'string', 'min:10', 'max:15'],
            'amount' => ['required', 'numeric', 'min:50'],
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
