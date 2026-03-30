<?php

namespace App\Http\Requests\Api\V1\Shares;

use Illuminate\Foundation\Http\FormRequest;

class StoreDividendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'total_amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
