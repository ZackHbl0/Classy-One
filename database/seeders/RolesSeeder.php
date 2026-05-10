<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Admin account
        User::updateOrCreate(
            ['email' => 'admin@osbt.com'],
            [
                'name'     => 'Directeur Admin',
                'password' => Hash::make('12345678'),
                'role'     => 'admin',
            ]
        );

        // 2. Secretaire account
        User::updateOrCreate(
            ['email' => 'secretaire@osbt.com'],
            [
                'name'     => 'Sécraitaire Opérations',
                'password' => Hash::make('12345678'),
                'role'     => 'secretaire',
            ]
        );
    }
}
