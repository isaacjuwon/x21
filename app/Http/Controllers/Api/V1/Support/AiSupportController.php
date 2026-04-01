<?php

namespace App\Http\Controllers\Api\V1\Support;

use App\Http\Controllers\Controller;
use App\Services\AiSupportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiSupportController extends Controller
{
    public function __invoke(Request $request, AiSupportService $aiSupport): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $answer = $aiSupport->ask($validated['message']);

        return response()->json(['answer' => $answer]);
    }
}
