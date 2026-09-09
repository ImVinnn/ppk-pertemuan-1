<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@jara.test'],
            [
                'name' => 'Admin JARA',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@jara.test'],
            [
                'name' => 'User JARA',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );
    }
}
