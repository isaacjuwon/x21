<?php

namespace App\Http\Requests\Api\V1\Kyc;

use App\Enums\Kyc\KycType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AutomaticKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(KycType::class)],
            'number' => ['required', 'string', 'digits:11'],
        ];
    }

    public function bodyParameters(): array { return []; }
}
