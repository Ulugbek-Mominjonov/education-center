<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            ['subject_id' => rand(1, 7), 'name' => 'Group 1', 'description' => 'Group 1', 'price' => 100000],
            ['subject_id' => rand(1, 7), 'name' => 'Group 2', 'description' => 'Group 2', 'price' => 100000],
            ['subject_id' => rand(1, 7), 'name' => 'Group 3', 'description' => 'Group 3', 'price' => 100000],
            ['subject_id' => rand(1, 7), 'name' => 'Group 4', 'description' => 'Group 4', 'price' => 100000],
            ['subject_id' => rand(1, 7), 'name' => 'Group 5', 'description' => 'Group 5', 'price' => 100000],
            ['subject_id' => rand(1, 7), 'name' => 'Group 6', 'description' => 'Group 6', 'price' => 100000],
            ['subject_id' => rand(1, 7), 'name' => 'Group 7', 'description' => 'Group 7', 'price' => 100000],
            ['subject_id' => rand(1, 7), 'name' => 'Group 8', 'description' => 'Group 8', 'price' => 100000],
            ['subject_id' => rand(1, 7), 'name' => 'Group 9', 'description' => 'Group 9', 'price' => 100000],
            ['subject_id' => rand(1, 7), 'name' => 'Group 10', 'description' => 'Group 10', 'price' => 100000],
        ];

        foreach ($groups as $group) {
            DB::table('groups')->insert($group);
        }
    }
}
