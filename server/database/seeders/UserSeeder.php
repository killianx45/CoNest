<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!User::where('email', 'sae4012@example.com')->exists()) {
            User::create([
                'name' => 'SAE',
                'email' => 'sae4012@example.com',
                'password' => Hash::make('Sae4012@'),
                'telephone' => '010101010',
                'role' => 'ROLE_ADMIN',
            ]);
        }

        if (!User::where('email', 'sae4012@user.com')->exists()) {
            User::create([
                'name' => 'SAEUser',
                'email' => 'sae4012@user.com',
                'password' => Hash::make('User4012@'),
                'telephone' => '0202020202',
                'role' => 'ROLE_USER',
            ]);
        }
    }
}
