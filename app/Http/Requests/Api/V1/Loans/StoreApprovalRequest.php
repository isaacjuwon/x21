<?php

namespace App\Http\Requests\Api\V1\Loans;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['notes' => ['nullable', 'string']]; }
    public function bodyParameters(): array { return []; }
}
