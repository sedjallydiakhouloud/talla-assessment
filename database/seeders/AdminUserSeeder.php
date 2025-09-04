<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Supprimer tous les utilisateurs existants
        User::truncate();

        // Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Utilisateur normal
        User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
