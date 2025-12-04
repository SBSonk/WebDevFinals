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
        // Create an admin user if none exists
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'password123');

        $user = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrator',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]
        );

        // Ensure the existing admin user has the correct role even if it was created earlier
        if (strtolower((string)($user->role)) !== 'admin') {
            $user->role = 'admin';
            $user->save();
        }
    }
}
