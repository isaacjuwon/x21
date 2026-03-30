<?php

namespace App\Http\Requests\Api\V1\Shares;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSharePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
