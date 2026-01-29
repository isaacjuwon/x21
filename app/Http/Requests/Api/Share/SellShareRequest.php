<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Share;

use App\Http\Payloads\Share\SellSharePayload;
use Illuminate\Foundation\Http\FormRequest;

class SellShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function payload(): SellSharePayload
    {
        return new SellSharePayload(
            quantity: (int) $this->integer('quantity'),
        );
    }
}
