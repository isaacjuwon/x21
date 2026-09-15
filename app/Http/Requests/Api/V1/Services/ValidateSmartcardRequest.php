<?php

namespace App\Http\Requests\Api\V1\Services;

use Illuminate\Foundation\Http\FormRequest;

class ValidateSmartcardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'smart_card_number' => ['required', 'string', 'min:10'],
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
