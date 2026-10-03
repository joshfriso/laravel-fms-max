<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Administrator', 'password' => 'password', 'role' => 'administrator',
        ]);
        User::updateOrCreate(['email' => 'viewer@example.com'], [
            'name' => 'Viewer', 'password' => 'password', 'role' => 'viewer',
        ]);
    }
}
