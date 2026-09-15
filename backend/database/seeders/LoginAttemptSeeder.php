<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\DatabaseSeeder;

class LoginAttemptSeeder extends Seeder
{
    public function run(): void
    {
        $alice = User::where('email', 'alice@example.local')->first();

        $attempts = [
            ['user_id' => $alice->id, 'username' => 'alice@example.local', 'success' => true, 'attempted_at' => now()->subDays(2)],
            ['user_id' => $alice->id, 'username' => 'alice@example.local', 'success' => false, 'attempted_at' => now()->subDays(3), 'failure_reason' => 'Invalid password'],
            ['user_id' => null, 'username' => 'unknown@example.local', 'success' => false, 'attempted_at' => now()->subDays(1), 'failure_reason' => 'User not found'],
        ];

        foreach ($attempts as $attempt) {
            LoginAttempt::create([
                'user_id' => $attempt['user_id'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'username' => $attempt['username'],
                'success' => $attempt['success'],
                'attempted_at' => $attempt['attempted_at'],
                'failure_reason' => $attempt['failure_reason'] ?? null,
            ]);
        }
    }
}
