<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Core security roles and administrative credentials
        $this->call(RolesSeeder::class);

        // 2. Comprehensive Master Demo Data Seeder (Educational structure, staff, students, parents, finances, grades, timetables, courses, official documents, events, notifications, audit logs)
        $this->call(DemoDataSeeder::class);
    }
}
