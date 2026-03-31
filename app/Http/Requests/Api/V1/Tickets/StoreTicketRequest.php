<?php

namespace App\Http\Requests\Api\V1\Tickets;

use App\Enums\Tickets\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10'],
            'priority' => ['nullable', new Enum(TicketPriority::class)],
        ];
    }

    public function bodyParameters(): array { return []; }
}
