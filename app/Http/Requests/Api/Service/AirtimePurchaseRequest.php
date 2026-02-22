<?php

namespace App\Http\Requests\Api\Service;

use App\Http\Requests\Api\ApiRequest;

class AirtimePurchaseRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'network_id' => ['required', 'exists:brands,id'],
            'amount' => ['required', 'numeric', 'min:100'],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                if ($this->amount && ! $this->user()->hasSufficientBalance((float) $this->amount)) {
                    $validator->errors()->add('amount', 'Insufficient wallet balance for this purchase.');
                }
            },
        ];
    }
}
