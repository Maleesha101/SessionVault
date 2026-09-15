<?php

namespace Database\Seeders;

use App\Models\PasswordResetToken;
use Illuminate\DatabaseSeeder;

class PasswordResetTokenSeeder extends Seeder
{
    public function run(): void
    {
        PasswordResetToken::create([
            'email' => 'alice@example.local',
            'token' => hash('sha256', 'lab-reset-token-001'),
            'created_at' => now()->subHours(2),
        ]);
    }
}
