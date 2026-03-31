<?php

namespace App\Http\Controllers\Api\V1\Support;

use App\Http\Resources\Api\V1\Support\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Support', 'FAQ and support resources')]
#[Authenticated]
class FaqController
{
    #[ResponseFromApiResource(FaqResource::class, Faq::class, collection: true)]
    public function index(): AnonymousResourceCollection
    {
        return FaqResource::collection(Faq::active()->get());
    }
}
