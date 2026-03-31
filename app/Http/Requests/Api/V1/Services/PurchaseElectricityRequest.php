<?php

namespace App\Http\Requests\Api\V1\Services;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseElectricityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'meter_number' => ['required', 'string', 'min:8'],
            'meter_type' => ['required', 'string', 'in:Prepaid,Postpaid'],
            'amount' => ['required', 'numeric', 'min:500'],
        ];
    }

    public function bodyParameters(): array { return []; }
}
