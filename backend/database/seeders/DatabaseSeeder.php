<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\LoginAttempt;
use App\Models\PasswordResetToken;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            LoginAttemptSeeder::class,
            PasswordResetTokenSeeder::class,
        ]);
    }
}
