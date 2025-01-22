<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathematics', 'description' => 'Mathematics'],
            ['name' => 'Physics', 'description' => 'Physics'],
            ['name' => 'Chemistry', 'description' => 'Chemistry'],
            ['name' => 'Biology', 'description' => 'Biology'],
            ['name' => 'Geography', 'description' => 'Geography'],
            ['name' => 'History', 'description' => 'History'],
            ['name' => 'English', 'description' => 'English'],
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->insert($subject);
        }
    }
}
