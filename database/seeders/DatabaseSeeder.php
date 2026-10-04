<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Data akun demo. */
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
