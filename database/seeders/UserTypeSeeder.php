<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserType;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserType::firstOrCreate(['name' => UserType::ADMIN]);
        UserType::firstOrCreate(['name' => UserType::PLAYER]);
        UserType::firstOrCreate(['name' => UserType::COACH]);
        UserType::firstOrCreate(['name' => UserType::STAFF]);
        UserType::firstOrCreate(['name' => UserType::PRESIDENT]);
    }
}