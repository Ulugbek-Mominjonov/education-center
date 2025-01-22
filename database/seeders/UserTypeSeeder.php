<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userTypes = [
            ['name' => 'Admin', 'description' => 'Administrator'],
            ['name' => 'teacher', 'description' => 'Teacher'],
            ['name' => 'student', 'description' => 'Student'],
        ];

        foreach ($userTypes as $userType) {
            DB::table('user_types')->insert($userType);
        }
    }
}
