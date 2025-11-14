<?php

namespace Tests\Feature\Feature;

use App\Models\Encounter;
use App\Models\Team;
use App\Models\User;
use App\Models\UserType;
use App\Services\MatchRecapImportService; // Import MatchRecapImportService
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum; // Import Sanctum
use Tests\TestCase;

class MatchRecapImportTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Encounter $encounter;

    private User $matchedPlayer;

    private User $otherPlayer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create all user types as in TeamControllerTest
        UserType::firstOrCreate(['name' => UserType::ADMIN]);
        UserType::firstOrCreate(['name' => UserType::PRESIDENT]);
        UserType::firstOrCreate(['name' => UserType::STAFF]);
        UserType::firstOrCreate(['name' => UserType::COACH]);
        UserType::firstOrCreate(['name' => UserType::PLAYER]);

        // Create and authenticate an admin user
        $adminUserTypeId = UserType::where('name', UserType::ADMIN)->first()->id;
        $this->adminUser = User::factory()->create(['user_type_id' => $adminUserTypeId]);
        Sanctum::actingAs($this->adminUser);

        // Create a team
        $team = Team::factory()->create();

        // Create players for the team
        $this->matchedPlayer = User::factory()->create(['licence_number' => 'LIC12345']);
        $this->otherPlayer = User::factory()->create(['licence_number' => 'LIC67890']);
        $team->users()->attach([$this->matchedPlayer->id, $this->otherPlayer->id]);

        // Create an encounter for the team
        $this->encounter = Encounter::factory()->create(['team_id' => $team->id]);
    }

    /** @test */
    public function it_prepares_recap_and_correctly_identifies_matched_and_unmatched_players()
    {
        // 1. Prepare the fake JSON file content
        $recapData = [
            'matchId' => 123456789,
            'teamName' => 'My Team',
            'opponentName' => 'Opponent',
            'score' => ['team' => 50, 'opponent' => 48],
            'players' => [
                ['id' => 'LIC12345', 'firstname' => 'John', 'lastname' => 'Doe'], // Matched
                ['id' => 'LIC99999', 'firstname' => 'Jane', 'lastname' => 'Smith'], // Unmatched
            ],
            'events' => [],
        ];

        $jsonContent = json_encode($recapData);
        $fakeFile = UploadedFile::fake()->createWithContent('recap.json', $jsonContent);

        // 2. Make the API call
        $response = $this->actingAs($this->adminUser)->postJson(
            "/api/matches/{$this->encounter->id}/recap/prepare",
            ['recapFile' => $fakeFile]
        );

        // 3. Assert the response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'matched',
            'unmatched',
            'official_players',
        ]);

        $response->assertJsonCount(1, 'matched');
        $response->assertJsonFragment(['id' => 'LIC12345'], 'matched');

        $response->assertJsonCount(1, 'unmatched');
        $response->assertJsonFragment(['id' => 'LIC99999'], 'unmatched');

        $response->assertJsonCount(2, 'official_players');
        $response->assertJsonFragment(['licence_number' => 'LIC12345'], 'official_players');
        $response->assertJsonFragment(['licence_number' => 'LIC67890'], 'official_players');
    }

    /** @test */
    public function it_returns_error_for_invalid_file_type()
    {
        $fakeFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->adminUser)->postJson(
            "/api/matches/{$this->encounter->id}/recap/prepare",
            ['recapFile' => $fakeFile]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('recapFile');
    }

    /** @test */
    public function it_returns_error_for_malformed_json()
    {
        $malformedJsonContent = '{"matchId": 123, "players": [}'; // Malformed JSON
        $fakeFile = UploadedFile::fake()->createWithContent('malformed.json', $malformedJsonContent);

        $response = $this->actingAs($this->adminUser)->postJson(
            "/api/matches/{$this->encounter->id}/recap/prepare",
            ['recapFile' => $fakeFile]
        );

        $response->assertStatus(422)
            ->assertJsonFragment(['recapFile' => 'Invalid JSON format.']);
    }

    /** @test */
    public function it_imports_recap_successfully()
    {
        // 1. Prepare data
        $recapData = [
            'matchId' => 123456789,
            'teamName' => 'My Team',
            'opponentName' => 'Opponent',
            'matchDate' => '2025-10-12',
            'location' => 'exterieur',
            'score' => ['team' => 58, 'opponent' => 62],
            'players' => [
                ['id' => 'LIC12345', 'firstname' => 'John', 'lastname' => 'Doe'],
                ['id' => 'LIC99999', 'firstname' => 'Jane', 'lastname' => 'Smith'], // This player won't be mapped
            ],
            'events' => [
                ['type' => 'shoot', 'playerId' => 'LIC12345', 'points' => 2, 'successful' => true, 'period' => 1, 'timestamp' => '2025-10-12T10:02:00.000Z'],
                ['type' => 'foul', 'playerId' => 'LIC12345', 'period' => 1, 'timestamp' => '2025-10-12T10:07:00.000Z'],
            ],
        ];

        $mappings = [
            ['json_player_id' => 'LIC12345', 'db_user_id' => $this->matchedPlayer->id],
        ];

        // 2. Make the API call
        $response = $this->actingAs($this->adminUser)->postJson(
            "/api/matches/{$this->encounter->id}/recap/import",
            [
                'recapData' => $recapData,
                'mappings' => $mappings,
            ]
        );

        // 3. Assert the response
        $response->assertStatus(200)
            ->assertJson(['message' => 'Match recap imported successfully.']);

        // 4. Assert database changes
        $this->assertDatabaseHas('encounters', [
            'id' => $this->encounter->id,
            'team_score' => 58,
            'opponent_score' => 62,
        ]);

        $this->assertDatabaseHas('encounter_stats', [
            'encounter_id' => $this->encounter->id,
            'json' => json_encode($recapData),
        ]);

        $this->assertDatabaseHas('individual_stats', [
            'encounter_id' => $this->encounter->id,
            'user_id' => $this->matchedPlayer->id,
            'json' => json_encode([
                'id' => 'LIC12345',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'events' => [
                    ['type' => 'shoot', 'playerId' => 'LIC12345', 'points' => 2, 'successful' => true, 'period' => 1, 'timestamp' => '2025-10-12T10:02:00.000Z'],
                    ['type' => 'foul', 'playerId' => 'LIC12345', 'period' => 1, 'timestamp' => '2025-10-12T10:07:00.000Z'],
                ],
            ]),
        ]);

        // Ensure the unmapped player is NOT in individual_stats
        $this->assertDatabaseMissing('individual_stats', [
            'encounter_id' => $this->encounter->id,
            'user_id' => $this->otherPlayer->id, // Assuming otherPlayer is not mapped
        ]);
    }

    /** @test */
    public function it_handles_transactional_failure_during_import()
    {
        // Initial state
        $initialScoreTeam = $this->encounter->team_score;
        $initialScoreOpponent = $this->encounter->opponent_score;

        $recapData = [
            'matchId' => 123456789,
            'teamName' => 'My Team',
            'opponentName' => 'Opponent',
            'matchDate' => '2025-10-12',
            'location' => 'exterieur',
            'score' => ['team' => 99, 'opponent' => 98], // New score
            'players' => [
                ['id' => 'LIC12345', 'firstname' => 'John', 'lastname' => 'Doe'],
            ],
            'events' => [
                ['type' => 'shoot', 'playerId' => 'LIC12345', 'points' => 2, 'successful' => true, 'period' => 1, 'timestamp' => '2025-10-12T10:02:00.000Z'],
                ['type' => 'foul', 'playerId' => 'LIC12345', 'period' => 1, 'timestamp' => '2025-10-12T10:07:00.000Z'],
            ],
        ];

        $mappings = [
            ['json_player_id' => 'LIC12345', 'db_user_id' => $this->matchedPlayer->id],
        ];

        // Mock the MatchRecapImportService to throw an exception
        $this->mock(MatchRecapImportService::class, function ($mock) {
            $mock->shouldReceive('execute')
                ->once()
                ->andThrow(new \Exception('Simulated database error'));
        });

        $response = $this->postJson(
            "/api/matches/{$this->encounter->id}/recap/import",
            [
                'recapData' => $recapData,
                'mappings' => $mappings,
            ]
        );

        $response->assertStatus(500)
            ->assertJson(['error' => 'An unexpected error occurred during import.']);

        // Assert that no changes were committed to the database
        $this->encounter->refresh();
        $this->assertEquals($initialScoreTeam, $this->encounter->team_score);
        $this->assertEquals($initialScoreOpponent, $this->encounter->opponent_score);

        $this->assertDatabaseMissing('encounter_stats', [
            'encounter_id' => $this->encounter->id,
        ]);

        $this->assertDatabaseMissing('individual_stats', [
            'encounter_id' => $this->encounter->id,
        ]);
    }

    /** @test */
    public function it_returns_error_for_invalid_mappings()
    {
        $recapData = [
            'matchId' => 123456789,
            'score' => ['team' => 58, 'opponent' => 62],
            'players' => [['id' => 'LIC12345', 'firstname' => 'John', 'lastname' => 'Doe']],
            'events' => [],
        ];

        $invalidMappings = [
            ['json_player_id' => 'LIC12345', 'db_user_id' => 99999], // Non-existent user ID
        ];

        $response = $this->actingAs($this->adminUser)->postJson(
            "/api/matches/{$this->encounter->id}/recap/import",
            [
                'recapData' => $recapData,
                'mappings' => $invalidMappings,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('mappings.0.db_user_id');
    }
}
