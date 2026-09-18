<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shares;

use App\Http\Resources\Api\V1\Shares\ShareOrderResource;
use App\Models\ShareOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Shares', 'Share orders and holdings')]
#[Authenticated]
final class IndexOrdersController
{
    #[QueryParam('type', 'string', description: 'Filter by order type: buy or sell', required: false, example: 'buy')]
    #[QueryParam('status', 'string', description: 'Filter by status: pending, approved, rejected, completed, cancelled', required: false, example: 'approved')]
    #[QueryParam('sort_by', 'string', description: 'Sort column: created_at or total_amount', required: false, example: 'created_at')]
    #[QueryParam('sort_direction', 'string', description: 'Sort direction: asc or desc', required: false, example: 'desc')]
    #[ResponseFromApiResource(ShareOrderResource::class, ShareOrder::class, collection: true, paginate: 10)]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:buy,sell'],
            'status' => ['nullable', 'string'],
            'sort_by' => ['nullable', 'string', 'in:created_at,total_amount'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDirection = $validated['sort_direction'] ?? 'desc';

        $orders = $request->user()
            ->shareOrders()
            ->when($validated['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy($sortBy, $sortDirection)
            ->paginate(10);

        return ShareOrderResource::collection($orders);
    }
}
