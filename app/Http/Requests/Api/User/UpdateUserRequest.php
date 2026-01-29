<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\User;

use App\Http\Payloads\User\UpdateUserPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'phone_number' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($this->user()->id)],
        ];
    }

    public function payload(): UpdateUserPayload
    {
        return new UpdateUserPayload(
            name: $this->string('name')->toString(),
            email: $this->string('email')->toString(),
            phoneNumber: $this->filled('phone_number') ? $this->string('phone_number')->toString() : null,
        );
    }
}
