<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("users")->insert([
            "user_name" => "admin",
            "user_type_id" => 1,
            "is_active" => 1,
            "is_attach" => 1,
            "password" => Hash::make("admin")
        ]);
    }
}
