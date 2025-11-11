<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateSeasonDTO;
use App\DTOs\UpdateSeasonDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\SeasonResource;
use App\Services\SeasonService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SeasonController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private SeasonService $seasonService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Season::class);
        $seasons = $this->seasonService->getAllSeasons();

        return SeasonResource::collection($seasons);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): SeasonResource
    {
        $this->authorize('create', Season::class);
        $dto = CreateSeasonDTO::fromRequest($request);
        $season = $this->seasonService->createSeason($dto);

        return new SeasonResource($season);
    }

    /**
     * Display the specified resource.
     */
    public function show(Season $season): SeasonResource
    {
        $this->authorize('view', $season);

        return new SeasonResource($season);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Season $season): SeasonResource
    {
        $this->authorize('update', $season);
        $dto = UpdateSeasonDTO::fromRequest($request);

        if (! $dto->hasData()) {
            return response()->json(['message' => 'Aucune donnée à mettre à jour'], 400);
        }

        $this->seasonService->updateSeason($season, $dto);

        return new SeasonResource($season->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Season $season): Response
    {
        $this->authorize('delete', $season);
        $this->seasonService->deleteSeason($season);

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
