<?php

namespace App\Http\Requests\Api\Service;

use App\Http\Requests\Api\ApiRequest;

class CablePurchaseRequest extends ApiRequest
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
            'plan_id' => ['required', 'exists:cable_plans,id'],
            'smartcard_number' => ['required', 'string', 'min:10', 'max:15'],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                $plan = \App\Models\CablePlan::find($this->plan_id);
                if ($plan && ! $this->user()->hasSufficientBalance((float) $plan->price)) {
                    $validator->errors()->add('plan_id', 'Insufficient wallet balance for this cable plan.');
                }
            },
        ];
    }
}
