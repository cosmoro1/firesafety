<?php

namespace Database\Seeders;

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
        // 1. Station Commander / Admin
        User::updateOrCreate(
            ['role' => 'admin'],
            [
                'name' => 'Station Commander',
                'email' => 'admin_bfp@gmail.com',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Fire Officer / Encoder
        User::updateOrCreate(
            ['role' => 'officer'],
            [
                'name' => 'Fire Officer Juan',
                'email' => 'officer_bfp@gmail.com',
                'password' => Hash::make('password'),
            ]
        );

        // 3. Records Officer / Clerk
        User::updateOrCreate(
            ['role' => 'clerk'],
            [
                'name' => 'Records Clerk Maria',
                'email' => 'records_bfp@gmail.com',
                'password' => Hash::make('password'),
            ]
        );
    }
}
