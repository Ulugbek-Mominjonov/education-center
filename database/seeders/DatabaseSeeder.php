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
        $this -> call([
            UserTypeSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            TeacherSeeder::class,
            TaskSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
            GroupSeeder::class
        ]);
    }
}
