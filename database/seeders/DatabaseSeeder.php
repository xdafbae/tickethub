<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['admin','user','gate_staff'] as $roleName) {
            Role::findOrCreate($roleName);
        }
        $this->call(AdminSeeder::class);
    }
}
