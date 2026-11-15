<?php

namespace App\Modules\History;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\History\Requests\CreateRequest;
use App\Modules\History\Resources\HistoryRecordResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Endpoints del historial; la lógica vive en {@see HistoryService}. */
class HistoryController extends Controller
{
    /** Inyecta las dependencias del controlador. */
    public function __construct(private readonly HistoryService $history)
    {
    }

    /** POST /api/history. */
    public function store(CreateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $record = $this->history->store($user, $request->validated());

        return response()->json([
            'data' => new HistoryRecordResource($record),
        ], 201);
    }

    /** GET /api/histories con paginación. */
    public function index(Request $request): JsonResponse
    {
        $perPage = $this->resolvePerPage($request);

        /** @var User $user */
        $user = $request->user();
        $page = $this->history->paginateForUser($user, $perPage);

        return response()->json([
            'data' => HistoryRecordResource::collection($page->items()),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /** DELETE /api/history/{id}: 204 o 404. */
    public function destroy(Request $request, string $id): JsonResponse|Response
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->history->deleteForUser($user, $id)) {
            return response()->json([
                'message' => 'Registro no encontrado.',
            ], 404);
        }

        return response()->noContent();
    }

    /** DELETE /api/histories. */
    public function clear(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $this->history->clearForUser($user);

        return response()->noContent();
    }

    /** Sanea `per_page` al rango 1..100 (default 20). */
    private function resolvePerPage(Request $request): int
    {
        $raw = $request->query('per_page', 20);

        if (! is_numeric($raw)) {
            return 20;
        }

        return max(1, min(100, (int) $raw));
    }
}