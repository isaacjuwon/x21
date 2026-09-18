<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Loans;

use App\Http\Resources\Api\V1\Loans\LoanResource;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Loans', 'Loan application and management')]
#[Authenticated]
final class IndexController
{
    #[QueryParam('status', 'string', description: 'Filter by status: active, approved, disbursed, repaid, rejected, defaulted', required: false, example: 'disbursed')]
    #[QueryParam('sort_by', 'string', description: 'Sort column: created_at or principal_amount', required: false, example: 'created_at')]
    #[QueryParam('sort_direction', 'string', description: 'Sort direction: asc or desc', required: false, example: 'desc')]
    #[ResponseFromApiResource(LoanResource::class, Loan::class, collection: true, paginate: 15)]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string'],
            'sort_by' => ['nullable', 'string', 'in:created_at,principal_amount'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDirection = $validated['sort_direction'] ?? 'desc';

        $loans = $request->user()
            ->loans()
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy($sortBy, $sortDirection)
            ->paginate(15);

        return LoanResource::collection($loans);
    }
}
