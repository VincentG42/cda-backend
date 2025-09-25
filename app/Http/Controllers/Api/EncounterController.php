<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Encounter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EncounterController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Encounter::class);

        return Encounter::with(['season', 'team'])
            ->where('happens_at', '>=', now())
            ->orderBy('happens_at')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Encounter::class);
        $validatedData = $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'team_id' => 'required|exists:teams,id',
            'opponent' => 'required|string|max:255',
            'is_at_home' => 'required|boolean',
            'happens_at' => 'required|date',
            'is_victory' => 'nullable|boolean',
        ]);

        $encounter = Encounter::create($validatedData);

        return response()->json($encounter, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Encounter $encounter)
    {
        $this->authorize('view', $encounter);

        // Load relationships for the single resource
        return $encounter->load(['season', 'team']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Encounter $encounter)
    {
        $this->authorize('update', $encounter);
        $validatedData = $request->validate([
            'season_id' => 'sometimes|required|exists:seasons,id',
            'team_id' => 'sometimes|required|exists:teams,id',
            'opponent' => 'sometimes|required|string|max:255',
            'is_at_home' => 'sometimes|required|boolean',
            'happens_at' => 'sometimes|required|date',
            'is_victory' => 'nullable|boolean',
        ]);

        $encounter->update($validatedData);

        return response()->json($encounter);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Encounter $encounter)
    {
        $this->authorize('delete', $encounter);
        $encounter->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Upload and process stats for a specific encounter.
     */
    public function uploadStats(Request $request, Encounter $encounter)
    {
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
