<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Encounter;
use App\Models\Event;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

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

        // Create Players and attach to teams
        for ($i = 1; $i <= 10; $i++) {
            $player = User::firstOrCreate(
                ['email' => "player{$i}@example.com"],
                [
                    'user_type_id' => $playerUserType->id,
                    'password' => Hash::make('password'),
                    'lastname' => $faker->lastName,
                    'firstname' => $faker->firstName,
                    'licence_number' => 'LIC' . $faker->unique()->randomNumber(6),
                    'has_to_change_password' => false,
                ]
            );
            $teamSeniorM->users()->syncWithoutDetaching([$player->id]);
        }

        // Create Events
        $adminUser = User::where('email', 'admin@admin.com')->first();

        Event::firstOrCreate(
            ['title' => 'Tournoi de Noël U15'],
            [
                'start_at' => '2024-12-20 14:00:00',
                'close_at' => '2024-12-20 16:00:00',
                'author_id' => $adminUser->id,
                'place' => 'Gymnase Municipal',
                'additionnal_info' => $faker->paragraph,
                'address' => $faker->address,
            ]
        );

        Event::firstOrCreate(
            ['title' => 'Portes Ouvertes'],
            [
                'start_at' => '2025-01-15 10:00:00',
                'close_at' => '2025-01-15 12:00:00',
                'author_id' => $adminUser->id,
                'place' => 'Salle Polyvalente',
                'additionnal_info' => $faker->paragraph,
                'address' => $faker->address,
            ]
        );

        // Create Encounters (Matches)
        Encounter::firstOrCreate(
            ['season_id' => $currentSeason->id, 'team_id' => $teamSeniorM->id, 'opponent' => 'Eagles Basketball', 'happens_at' => '2025-11-10 18:00:00'],
            [
                'is_at_home' => true,
                'is_victory' => null,
            ]
        );

        Encounter::firstOrCreate(
            ['season_id' => $currentSeason->id, 'team_id' => $teamU18F->id, 'opponent' => 'Thunder Bolts', 'happens_at' => '2025-11-17 16:00:00'],
            [
                'is_at_home' => false,
                'is_victory' => null,
            ]
        );
    }
}
