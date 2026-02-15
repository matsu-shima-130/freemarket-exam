<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => '出品者ユーザー',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
