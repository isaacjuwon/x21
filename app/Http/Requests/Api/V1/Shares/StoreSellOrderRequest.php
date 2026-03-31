<?php

namespace App\Http\Requests\Api\V1\Shares;

use Illuminate\Foundation\Http\FormRequest;

class StoreSellOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['quantity' => ['required', 'integer', 'min:1']]; }
    public function bodyParameters(): array { return []; }
}
