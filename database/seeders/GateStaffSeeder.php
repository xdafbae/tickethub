<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class GateStaffSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::findOrCreate('gate_staff');

        $email = env('GATE_EMAIL', 'gate@example.com');
        $password = env('GATE_PASSWORD', 'gate12345');

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Gate Staff', 'password' => Hash::make($password), 'email_verified_at' => now()]
        );

        if (! $user->hasRole('gate_staff')) {
            $user->assignRole($role);
        }
    }
}