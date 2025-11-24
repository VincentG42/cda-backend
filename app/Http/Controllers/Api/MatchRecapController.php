<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Encounter;
use App\Services\MatchRecapImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatchRecapController extends Controller
{
    /**
     * Prepares the match recap for import by validating the JSON file
     * and returning its content for player reconciliation.
     */
    public function prepareRecap(Request $request, string $encounter_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'recapFile' => 'required|file',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::error('Recap validation failed', [
                'errors' => $validator->errors()->toArray(),
                'files' => $request->allFiles(),
                'post' => $request->all(),
            ]);

            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('recapFile');
        $content = $file->getContent();
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['errors' => ['recapFile' => 'Invalid JSON format.']], 422);
        }

        $encounter = Encounter::with('team.users')->find($encounter_id);

        if (! $encounter) {
            return response()->json(['error' => 'Encounter not found.'], 404);
        }

        $officialPlayers = $encounter->team->users;
        $jsonPlayers = collect($data['players'] ?? []);

        $officialLicenceNumbers = $officialPlayers->pluck('licence_number')->all();

        $matched = [];
        $unmatched = [];

        foreach ($jsonPlayers as $jsonPlayer) {
            $licenceId = $jsonPlayer['id'] ?? null;
            if ($licenceId && in_array($licenceId, $officialLicenceNumbers)) {
                $matched[] = $jsonPlayer;
            } else {
                $unmatched[] = $jsonPlayer;
            }
        }

        return response()->json([
            'recap_data' => $data, // Include the full parsed JSON data
            'matched' => $matched,
            'unmatched' => $unmatched,
            'official_players' => $officialPlayers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'licence_number' => $user->licence_number,
                ];
            }),
        ]);
    }

    /**
     * Imports the validated match recap data into the database.
     */
    public function importRecap(Request $request, string $encounter_id, MatchRecapImportService $importer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'recapData' => 'required|array',
            'mappings' => 'required|array',
            'mappings.*.json_player_id' => 'required|string',
            'mappings.*.db_user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $encounter = Encounter::find($encounter_id);
        if (! $encounter) {
            return response()->json(['error' => 'Encounter not found.'], 404);
        }

        try {
            $importer->execute(
                $encounter,
                $request->input('recapData'),
                $request->input('mappings')
            );
        } catch (\Throwable $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Match Recap Import Failed: '.$e->getMessage());

            return response()->json(['error' => 'An unexpected error occurred during import.'], 500);
        }

        return response()->json(['message' => 'Match recap imported successfully.']);
    }
}
