<?php

namespace App\Http\Requests\Api\Service;

use App\Http\Requests\Api\ApiRequest;

class ElectricityPurchaseRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'operator_id' => ['required', 'exists:brands,id'],
            'meter_type' => ['required', 'string', 'in:prepaid,postpaid'],
            'meter_number' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:100'],
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
                    $validator->errors()->add('amount', 'Insufficient wallet balance for this electricity payment.');
                }
            },
        ];
    }
}
