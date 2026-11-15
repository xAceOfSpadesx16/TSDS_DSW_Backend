<?php

namespace App\Modules\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/** Endpoint del aviso legal; el texto vive en {@see LegalService}. */
class LegalController extends Controller
{
    public function __construct(private readonly LegalService $legal)
    {
    }

    /** GET /api/legal/disclaimer. */
    public function disclaimer(): JsonResponse
    {
        return response()->json($this->legal->disclaimer());
    }
}