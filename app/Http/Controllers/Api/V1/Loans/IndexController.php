<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Loans;

use App\Http\Resources\Api\V1\Loans\LoanResource;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Loans', 'Loan application and management')]
#[Authenticated]
final class IndexController
{
    #[ResponseFromApiResource(LoanResource::class, Loan::class, collection: true, paginate: 15)]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        return LoanResource::collection(
            $request->user()->loans()->latest()->paginate(15)
        );
    }
}
