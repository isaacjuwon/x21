<?php

namespace App\Http\Resources\Api\V1\Loans;

use App\Enums\Loans\LoanScheduleEntryStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Computed stats shown on the loan detail view — only calculate when
        // schedule entries are loaded or when this is a single-resource response.
        $scheduleLoaded = $this->relationLoaded('scheduleEntries');
        $entries = $scheduleLoaded ? $this->scheduleEntries : null;

        $totalPayable = $entries
            ? (float) $entries->sum('instalment_amount')
            : null;

        $totalPaid = $entries
            ? (float) $entries->where('status', LoanScheduleEntryStatus::Paid)->sum('instalment_amount')
            : null;

        $nextRepayment = $entries
            ? $entries->where('status', LoanScheduleEntryStatus::Pending)->sortBy('due_date')->first()
            : null;

        return [
            'id' => $this->id,
            'principal_amount' => $this->principal_amount,
            'outstanding_balance' => $this->outstanding_balance,
            'interest_rate' => $this->interest_rate,
            'repayment_term_months' => $this->repayment_term_months,
            'interest_method' => $this->interest_method?->value,
            'status' => $this->status?->value,
            'disbursed_at' => $this->disbursed_at,
            'eligibility_passed' => $this->eligibility_passed,
            'notes' => $this->notes,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            // Computed display fields — only present when schedule is loaded
            'total_payable' => $totalPayable,
            'total_paid' => $totalPaid,
            'next_repayment' => $nextRepayment ? [
                'instalment_number' => $nextRepayment->instalment_number,
                'due_date' => $nextRepayment->due_date,
                'instalment_amount' => $nextRepayment->instalment_amount,
            ] : null,
        ];
    }
}
