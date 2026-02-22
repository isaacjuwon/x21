<?php

namespace App\Http\Requests\Api\Auth;

use App\Actions\Fortify\PasswordValidationRules;
use App\Http\Requests\Api\ApiRequest;

class RegisterRequest extends ApiRequest
{
    use PasswordValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
        ];
    }
}

