<?php

namespace App\Http\Requests\Api\Service;

use App\Http\Requests\Api\ApiRequest;

class EducationPurchaseRequest extends ApiRequest
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
            'plan_id' => ['required', 'exists:education_plans,id'],
            'email' => ['required', 'email'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                $plan = \App\Models\EducationPlan::find($this->plan_id);
                $quantity = $this->quantity ?? 1;
                $totalPrice = $plan ? (float) $plan->price * $quantity : 0;
                
                if ($plan && ! $this->user()->hasSufficientBalance($totalPrice)) {
                    $validator->errors()->add('plan_id', 'Insufficient wallet balance for this education purchase.');
                }
            },
        ];
    }
}
