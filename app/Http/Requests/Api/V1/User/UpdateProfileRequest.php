<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone_number' => [
                'nullable',
                'string',
                'min:10',
                'max:15',
                Rule::unique('users', 'phone_number')->ignore($this->user()?->id),
            ],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function bodyParameters(): array { return []; }
}
