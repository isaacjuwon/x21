<?php

namespace App\Http\Requests\Api\Service;

use App\Http\Requests\Api\ApiRequest;

class DataPurchaseRequest extends ApiRequest
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
            'data_type' => ['required', 'string'],
            'plan_id' => ['required', 'exists:data_plans,id'],
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
                $plan = \App\Models\DataPlan::find($this->plan_id);
                if ($plan && ! $this->user()->hasSufficientBalance((float) $plan->price)) {
                    $validator->errors()->add('plan_id', 'Insufficient wallet balance for this data plan.');
                }
            },
        ];
    }
}
