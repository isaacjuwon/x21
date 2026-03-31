<?php

namespace App\Http\Requests\Api\V1\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:5'],
        ];
    }

    public function bodyParameters(): array { return []; }
}
