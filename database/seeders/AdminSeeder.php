<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTE: This is the single admin user for the system.
        // It's recommended to rotate this password periodically.
        Admin::updateOrCreate(
            ['username' => 'devparth_admin'],
            [
                'password' => Hash::make('Dev$arth966-mG_xT'),
            ]
        );
    }
}
