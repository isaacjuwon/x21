<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Loans;

use App\Http\Resources\Api\V1\Loans\LoanResource;
use App\Models\Loan;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Loans', 'Loan application and management')]
#[Authenticated]
final class ShowController
{
    #[ResponseFromApiResource(LoanResource::class, Loan::class)]
    #[Response(['message' => 'Not Found'], status: 404)]
    public function __invoke(Request $request, Loan $loan): LoanResource
    {
        abort_unless($loan->user_id === $request->user()->id, 404);

        return new LoanResource($loan);
    }
}
