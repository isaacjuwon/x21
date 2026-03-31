<?php

namespace App\Http\Requests\Api\V1\Services;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseCableTvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'plan_id' => ['required', 'integer', 'exists:cable_plans,id'],
            'smart_card_number' => ['required', 'string', 'min:10'],
        ];
    }

    public function bodyParameters(): array { return []; }
}
