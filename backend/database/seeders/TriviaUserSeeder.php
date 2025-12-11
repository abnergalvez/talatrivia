<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TriviaUserSeeder extends Seeder
{
    public function run()
    {
        DB::table('trivia_user')->truncate();

        $user1 = DB::table('users')->where('email', 'player1@trivia.com')->first();
        $trivia1 = DB::table('trivia')->where('name', 'Introducción a Recursos Humanos')->first();

        $user2 = DB::table('users')->where('email', 'player2@trivia.com')->first();
        $trivia2 = DB::table('trivia')->where('name', 'Liderazgo y Gestión del Talento')->first();

        DB::table('trivia_user')->insert([
            'user_id' => $user1->id,
            'trivia_id' => $trivia1->id,
            'score' => 2,
            'completed_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ],
        [
            'user_id' => $user2->id,
            'trivia_id' => $trivia2->id,
            'score' => 0,
            'completed_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
