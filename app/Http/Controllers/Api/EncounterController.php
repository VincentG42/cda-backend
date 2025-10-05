<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateEncounterDTO;
use App\DTOs\EncounterFilterDTO;
use App\DTOs\UpdateEncounterDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\EncounterResource;
use App\Models\Encounter;
use App\Services\EncounterService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EncounterController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private EncounterService $encounterService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Encounter::class);
        $dto = EncounterFilterDTO::fromRequest($request);
        $encounters = $this->encounterService->getFilteredEncounters($dto);

        $encounters->load('team');

        return EncounterResource::collection($encounters);
    }

    public function store(Request $request): EncounterResource
    {
        $this->authorize('create', Encounter::class);
        $dto = CreateEncounterDTO::fromRequest($request);
        $encounter = $this->encounterService->createEncounter($dto);

        return new EncounterResource($encounter);
    }

    public function show(Encounter $encounter): EncounterResource
    {
        $this->authorize('view', $encounter);

        return new EncounterResource($encounter->load(['season', 'team']));
    }

    public function update(Request $request, Encounter $encounter): EncounterResource
    {
        $this->authorize('update', $encounter);
        $dto = UpdateEncounterDTO::fromRequest($request);
        $this->encounterService->updateEncounter($encounter, $dto);

        return new EncounterResource($encounter->fresh()->load(['season', 'team']));
    }

    public function destroy(Encounter $encounter): Response
    {
        $this->authorize('delete', $encounter);
        $this->encounterService->deleteEncounter($encounter);

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function uploadStats(Request $request, Encounter $encounter)
    {
        // This logic can be moved to a service in the future
        $request->validate([
            'stats_file' => 'required|file|mimes:json',
        ]);

        $file = $request->file('stats_file');
        $content = $file->getContent();
        $statsData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid JSON file.'], Response::HTTP_BAD_REQUEST);
        }

        // TODO: Implement the logic to process and save the stats.
        return response()->json([
            'message' => 'Stats file uploaded and parsed successfully.',
            'encounter_id' => $encounter->id,
            'parsed_data' => $statsData,
        ]);
    }
}
