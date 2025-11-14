<?php

namespace App\Services;

use App\Models\Encounter;
use App\Models\EncounterStat;
use App\Models\IndividualStat;
use Illuminate\Support\Facades\DB;
use Throwable;

class MatchRecapImportService
{
    /**
     * @throws Throwable
     */
    public function execute(Encounter $encounter, array $recapData, array $mappings): void
    {
        DB::transaction(function () use ($encounter, $recapData, $mappings) {
            // 1. Update encounter score
            $encounter->update([
                'team_score' => $recapData['score']['team'],
                'opponent_score' => $recapData['score']['opponent'],
            ]);

            // 2. Store raw JSON recap
            EncounterStat::updateOrCreate(
                ['encounter_id' => $encounter->id],
                ['json' => json_encode($recapData)]
            );

            // 3. Process and store individual stats
            $eventsByPlayer = collect($recapData['events'])->groupBy('playerId');
            $playerMappings = collect($mappings)->pluck('db_user_id', 'json_player_id');

            foreach ($recapData['players'] as $jsonPlayer) {
                $jsonPlayerId = $jsonPlayer['id'];
                $dbUserId = $playerMappings->get($jsonPlayerId);

                if (! $dbUserId) {
                    continue; // Skip if no mapping was provided for this player
                }

                $playerEvents = $eventsByPlayer->get($jsonPlayerId, collect())->all();

                $individualJson = json_encode([
                    'id' => $jsonPlayer['id'],
                    'firstname' => $jsonPlayer['firstname'],
                    'lastname' => $jsonPlayer['lastname'],
                    'events' => $playerEvents,
                ]);

                IndividualStat::updateOrCreate(
                    [
                        'encounter_id' => $encounter->id,
                        'user_id' => $dbUserId,
                    ],
                    ['json' => $individualJson]
                );
            }
        });
    }
}
