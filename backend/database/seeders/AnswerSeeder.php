<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnswerSeeder extends Seeder
{
    public function run()
    {
        DB::table('answers')->truncate();

        $user = DB::table('users')->where('email', 'player1@trivia.com')->first();
        $trivia = DB::table('trivia')->where('name', 'Introducción a Recursos Humanos')->first();

        $questions = DB::table('questions')
            ->where('trivia_id', $trivia->id)
            ->get();

        foreach ($questions as $q) {

            $correctOpt = DB::table('options')
                ->where('question_id', $q->id)
                ->where('is_correct', true)
                ->first();

            DB::table('answers')->insert([
                'user_id' => $user->id,
                'trivia_id' => $trivia->id,
                'question_id' => $q->id,
                'option_id' => $correctOpt->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
