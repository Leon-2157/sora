<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sora.test'],
            [
                'name' => 'Admin Sora',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@sora.test'],
            [
                'name' => 'Staff Sora',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]
        );
    }
}
