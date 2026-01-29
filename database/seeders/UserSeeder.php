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
        User::create([
            'name' => 'Station Commander',
            'email' => 'admin@bfp.gov.ph',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Fire Officer / Encoder
        User::create([
            'name' => 'Fire Officer Juan',
            'email' => 'officer@bfp.gov.ph',
            'password' => Hash::make('password'),
            'role' => 'officer',
        ]);

        // 3. Records Officer / Clerk
        User::create([
            'name' => 'Records Clerk Maria',
            'email' => 'records@bfp.gov.ph',
            'password' => Hash::make('password'),
            'role' => 'clerk',
        ]);
    }
}