<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@riderhub.test',
            'phone' => '+639170000001',
            'role' => 'admin',
            'password' => bcrypt('123'),
        ]);

        User::factory()->create([
            'name' => 'Rider User',
            'email' => 'rider@riderhub.test',
            'phone' => '+639170000002',
            'role' => 'rider',
            'password' => bcrypt('123'),
        ]);

        User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@riderhub.test',
            'phone' => '+639170000003',
            'role' => 'customer',
            'password' => bcrypt('123'),
        ]);
    }
}
