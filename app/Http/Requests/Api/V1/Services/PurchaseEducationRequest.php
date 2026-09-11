<?php

namespace App\Http\Requests\Api\V1\Services;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'plan_id' => ['required', 'integer', 'exists:education_plans,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
