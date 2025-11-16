<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::findOrCreate('admin');

        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'admin12345');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole('admin')) {
            $user->assignRole($role);
        }
    }
}
