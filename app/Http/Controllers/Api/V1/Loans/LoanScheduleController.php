<?php

namespace App\Http\Controllers\Api\V1\Loans;

use App\Http\Resources\Api\V1\Loans\LoanScheduleResource;
use App\Models\Loan;
use App\Models\LoanScheduleEntry;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Loans', 'Loan application and management')]
#[Authenticated]
class LoanScheduleController
{
    use AuthorizesRequests;

    #[ResponseFromApiResource(LoanScheduleResource::class, LoanScheduleEntry::class, collection: true)]
    public function __invoke(Request $request, Loan $loan): AnonymousResourceCollection
    {
        $this->authorize('view', $loan);

        return LoanScheduleResource::collection(
            $loan->scheduleEntries()->orderBy('instalment_number')->get()
        );
    }
}
