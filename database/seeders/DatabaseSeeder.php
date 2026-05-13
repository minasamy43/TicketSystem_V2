<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        // ADMIN ACCOUNT (IMPORTANT)
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Mina',
                'password' => Hash::make('password'),
                'role' => 1,
            ]
        );
        // Normal agent
        User::updateOrCreate(
            ['email' => 'agent@example.com'],
            [
                'name' => 'Mark',
                'password' => Hash::make('password'),
                'role' => 0,
            ]
        );
        // User ACCOUNT
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Ashraf',
                'password' => Hash::make('password'),
                'role' => 2,
            ]
        );

        // System Preferences
        $this->call(SettingsSeeder::class);

        // Knowledge Base
        $this->call(KnowledgeBaseSeeder::class);
    }
}
