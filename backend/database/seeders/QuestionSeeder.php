<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        DB::table('questions')->truncate();

        $easy = DB::table('levels')->where('name', 'Fácil')->first()->id;
        $medium = DB::table('levels')->where('name', 'Medio')->first()->id;
        $difficult = DB::table('levels')->where('name', 'Difícil')->first()->id;

        $intro = DB::table('trivia')->where('name', 'Introducción a Recursos Humanos')->first()->id;
        $leadership = DB::table('trivia')->where('name', 'Liderazgo y Gestión del Talento')->first()->id;

        DB::table('questions')->insert([
            [
                'trivia_id' => $intro,
                'level_id' => $easy,
                'description' => '¿Qué es el reclutamiento en Recursos Humanos?',
                'time' => 15,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'trivia_id' => $intro,
                'level_id' => $medium,
                'description' => '¿Cuál es el objetivo principal de una entrevista laboral?',
                'time' => 20,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'trivia_id' => $intro,
                'level_id' => $medium,
                'description' => '¿Qué se entiende por “clima laboral”?',
                'time' => 20,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            [
                'trivia_id' => $leadership,
                'level_id' => $easy,
                'description' => '¿Qué característica distingue a un líder efectivo?',
                'time' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'trivia_id' => $leadership,
                'level_id' => $difficult,
                'description' => '¿Qué es la evaluación de desempeño?',
                'time' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
    }
}
