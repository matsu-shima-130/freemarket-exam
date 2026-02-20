<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 出品者A（CO01〜CO05担当）
        User::updateOrCreate(
            ['email' => 'seller_a@example.com'],
            [
                'name' => '出品者A',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 出品者B（CO06〜CO10担当）
        User::updateOrCreate(
            ['email' => 'seller_b@example.com'],
            [
                'name' => '出品者B',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 未紐づけユーザー（何も出品しない想定）
        User::updateOrCreate(
            ['email' => 'user_free@example.com'],
            [
                'name' => '未紐づけユーザー',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
