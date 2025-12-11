<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class RoleAndUserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->truncate();
        DB::table('roles')->truncate();

        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => 'admin',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $playerRoleId = DB::table('roles')->insertGetId([
            'name' => 'player',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@trivia.com',
            'password' => Hash::make('admin123'),
            'api_token' => Str::random(60),
            'role_id' => $adminRoleId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('users')->insert([
            [
                'name' => 'Player One',
                'email' => 'player1@trivia.com',
                'password' => Hash::make('player123'),
                'api_token' => Str::random(60),
                'role_id' => $playerRoleId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Player Two',
                'email' => 'player2@trivia.com',
                'password' => Hash::make('player123'),
                'api_token' => Str::random(60),
                'role_id' => $playerRoleId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
    }
}
