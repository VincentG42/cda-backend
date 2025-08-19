<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUserType = UserType::where('name', UserType::ADMIN)->first();

        if ($adminUserType) {
            User::firstOrCreate(
                ['email' => 'admin@admin.com'],
                [
                    'user_type_id' => $adminUserType->id,
                    'password' => Hash::make('password1234'),
                    'lastname' => 'Admin',
                    'firstname' => 'Super',
                    'licence_number' => 'ADMIN001',
                    'has_to_change_password' => false,
                ]
            );
        }
    }
}
