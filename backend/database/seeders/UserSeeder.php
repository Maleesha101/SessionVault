<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.local',
                'password' => 'LabPass123!',
                'role' => 'admin',
                'is_disabled' => false,
            ],
            [
                'name' => 'Alice Anderson',
                'email' => 'alice@example.local',
                'password' => 'LabPass123!',
                'role' => 'user',
                'is_disabled' => false,
            ],
            [
                'name' => 'Bob Brown',
                'email' => 'bob@example.local',
                'password' => 'LabPass123!',
                'role' => 'user',
                'is_disabled' => false,
            ],
            [
                'name' => 'Charlie Clark',
                'email' => 'charlie@example.local',
                'password' => 'LabPass123!',
                'role' => 'user',
                'is_disabled' => false,
            ],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password'],
                'role' => $user['role'],
                'is_disabled' => $user['is_disabled'],
            ]);
        }
    }
}
