<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 10 users using the User factory
        User::factory(10)->create();

        // If you have other seeders, you can call them here
        $this->call(ProductSeeder::class);
    }
}
