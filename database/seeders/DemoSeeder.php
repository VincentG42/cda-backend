<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Encounter;
use App\Models\Event;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Models\UserType;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR');

        // User Types (ensure they exist)
        $playerUserType = UserType::firstOrCreate(['name' => UserType::PLAYER]);
        $coachUserType = UserType::firstOrCreate(['name' => UserType::COACH]);
        $staffUserType = UserType::firstOrCreate(['name' => UserType::STAFF]);
        $presidentUserType = UserType::firstOrCreate(['name' => UserType::PRESIDENT]);
        $adminUserType = UserType::firstOrCreate(['name' => UserType::ADMIN]);

        // Create specific users
        User::firstOrCreate(
            ['email' => 'president@example.com'],
            [
                'user_type_id' => $presidentUserType->id,
                'password' => Hash::make('password'),
                'lastname' => 'Président',
                'firstname' => 'Monsieur',
                'licence_number' => 'PRES001',
                'has_to_change_password' => false,
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'user_type_id' => $staffUserType->id,
                'password' => Hash::make('password'),
                'lastname' => 'Staff',
                'firstname' => 'Madame',
                'licence_number' => 'STAFF001',
                'has_to_change_password' => false,
            ]
        );

        $coach = User::firstOrCreate(
            ['email' => 'coach@example.com'],
            [
                'user_type_id' => $coachUserType->id,
                'password' => Hash::make('password'),
                'lastname' => 'Coach',
                'firstname' => 'Coach',
                'licence_number' => 'COACH001',
                'has_to_change_password' => false,
            ]
        );

        // Create Categories
        $seniorM = Category::firstOrCreate(['title' => 'Senior M'], ['gender' => 'M']);
        $u18F = Category::firstOrCreate(['title' => 'U18 F'], ['gender' => 'F']);
        $u15M = Category::firstOrCreate(['title' => 'U15 M'], ['gender' => 'M']);
        $u13F = Category::firstOrCreate(['title' => 'U13 F'], ['gender' => 'F']);

        // Create Seasons
        $currentSeason = Season::firstOrCreate(['name' => 'Saison 2025-2026'], ['start_date' => '2025-09-01', 'end_date' => '2026-06-30', 'is_active' => true]);

        // Create Teams
        $teamSeniorM = Team::firstOrCreate(
            ['name' => 'CCSLR Senior M'],
            [
                'category_id' => $seniorM->id,
                'season_id' => $currentSeason->id,
                'coach_id' => $coach->id,
            ]
        );

        $teamU18F = Team::firstOrCreate(
            ['name' => 'CCSLR U18 F'],
            [
                'category_id' => $u18F->id,
                'season_id' => $currentSeason->id,
                'coach_id' => $coach->id,
            ]
        );

        $teamU15M = Team::firstOrCreate(
            ['name' => 'CCSLR U15 M'],
            [
                'category_id' => $u15M->id,
                'season_id' => $currentSeason->id,
                'coach_id' => $coach->id,
            ]
        );

        $teamU13F = Team::firstOrCreate(
            ['name' => 'CCSLR U13 F'],
            [
                'category_id' => $u13F->id,
                'season_id' => $currentSeason->id,
                'coach_id' => $coach->id,
            ]
        );

        $allTeams = Team::all();

        // Create Players and attach to teams
        for ($i = 1; $i <= 10; $i++) {
            $player = User::firstOrCreate(
                ['email' => "player{$i}@example.com"],
                [
                    'user_type_id' => $playerUserType->id,
                    'password' => Hash::make('password'),
                    'lastname' => $faker->lastName,
                    'firstname' => $faker->firstName,
                    'licence_number' => 'LIC'.$faker->unique()->randomNumber(6),
                    'has_to_change_password' => false,
                ]
            );
            $teamSeniorM->users()->syncWithoutDetaching([$player->id]);
        }

        // Create Events
        $adminUser = User::where('email', 'admin@admin.com')->first();

        // Define key dates for 2025
        $dec15 = now()->year(2025)->month(12)->day(15)->startOfDay();
        $dec20 = now()->year(2025)->month(12)->day(20)->startOfDay();
        $dec21 = now()->year(2025)->month(12)->day(21)->startOfDay();
        $dec23 = now()->year(2025)->month(12)->day(23)->startOfDay();
        $jan2026 = now()->year(2026)->month(1)->day(15)->startOfDay();
        $may2026 = now()->year(2026)->month(5)->day(25)->startOfDay();

        // Existing event updated to 2025
        Event::firstOrCreate(
            ['title' => 'Tournoi de Noël U15'],
            [
                'start_at' => $dec20->copy()->addHours(14),
                'close_at' => $dec20->copy()->addHours(16),
                'author_id' => $adminUser->id,
                'place' => 'Gymnase Municipal',
                'additionnal_info' => $faker->paragraph,
                'address' => $faker->address,
            ]
        );

        // New events after Dec 15th
        Event::firstOrCreate(
            ['title' => 'Marché de Noël du Club'],
            [
                'start_at' => $dec15->copy()->addDays(3)->addHours(10), // Dec 18
                'close_at' => $dec15->copy()->addDays(4)->addHours(18), // Dec 19
                'author_id' => $adminUser->id,
                'place' => 'Salle des Fêtes',
                'additionnal_info' => $faker->paragraph,
                'address' => $faker->address,
            ]
        );

        Event::firstOrCreate(
            ['title' => 'Noël des Enfants'],
            [
                'start_at' => $dec23->copy()->addHours(14),
                'close_at' => $dec23->copy()->addHours(17),
                'author_id' => $adminUser->id,
                'place' => 'Gymnase Principal',
                'additionnal_info' => $faker->paragraph,
                'address' => $faker->address,
            ]
        );

        Event::firstOrCreate(
            ['title' => 'Gala de Fin de Saison'],
            [
                'start_at' => $may2026->copy()->addHours(19),
                'close_at' => $may2026->copy()->addHours(23),
                'author_id' => $adminUser->id,
                'place' => 'Salle de Réception',
                'additionnal_info' => $faker->paragraph,
                'address' => $faker->address,
            ]
        );

        // Create Encounters (Matches)
        // 5 matches before Dec 15th
        for ($i = 0; $i < 5; $i++) {
            $team = $allTeams->random();
            Encounter::firstOrCreate(
                [
                    'season_id' => $currentSeason->id,
                    'team_id' => $team->id,
                    'opponent' => $faker->company.' Basketball',
                    'happens_at' => $dec15->copy()->subDays(rand(1, 30))->addHours(rand(10, 20)),
                ],
                [
                    'is_at_home' => $faker->boolean,
                    'is_victory' => null,
                ]
            );
        }

        // Matches after Dec 15th
        for ($i = 0; $i < 3; $i++) {
            $team = $allTeams->random();
            Encounter::firstOrCreate(
                [
                    'season_id' => $currentSeason->id,
                    'team_id' => $team->id,
                    'opponent' => $faker->company.' Basketball',
                    'happens_at' => $dec15->copy()->addDays(rand(10, 40))->addHours(rand(10, 20)),
                ],
                [
                    'is_at_home' => $faker->boolean,
                    'is_victory' => null,
                ]
            );
        }

        // Ensure all teams have a match for the weekend after Dec 15th (Dec 20-21, 2025)
        foreach ($allTeams as $team) {
            Encounter::firstOrCreate(
                [
                    'season_id' => $currentSeason->id,
                    'team_id' => $team->id,
                    'opponent' => $faker->company.' Basketball',
                    'happens_at' => $faker->dateTimeBetween($dec20->copy()->addHours(10), $dec21->copy()->addHours(20)),
                ],
                [
                    'is_at_home' => $faker->boolean,
                    'is_victory' => null,
                ]
            );
        }
    }
}
