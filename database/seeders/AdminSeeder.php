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
        $username = trim((string) env('DEVOS_ADMIN_USERNAME', ''));
        $password = (string) env('DEVOS_ADMIN_PASSWORD', '');

        if ($username === '' || $password === '') {
            $this->command?->warn('Skipping AdminSeeder: DEVOS_ADMIN_USERNAME / DEVOS_ADMIN_PASSWORD not set.');

            return;
        }

        // NOTE: This is the single admin user for the system.
        // Provide credentials via environment variables instead of source control.
        Admin::updateOrCreate(
            ['username' => $username],
            [
                'password' => Hash::make($password),
            ]
        );
    }
}
