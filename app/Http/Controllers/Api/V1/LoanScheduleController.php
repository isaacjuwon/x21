<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Loans\LoanStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LoanScheduleResource;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LoanScheduleController extends Controller
{
    public function index(Request $request, Loan $loan): AnonymousResourceCollection
    {
        $this->authorize('view', $loan);

        if ($loan->status !== LoanStatus::Disbursed) {
            abort(422, 'Loan schedule is only available for disbursed loans.');
        }

        return LoanScheduleResource::collection(
            $loan->scheduleEntries()->orderBy('instalment_number')->get()
        );
    }
}
