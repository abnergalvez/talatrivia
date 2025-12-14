<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TriviaSeeder extends Seeder
{
    public function run()
    {
        DB::table('trivia')->truncate();

        DB::table('trivia')->insert([
            [
                'name' => 'Introducción a Recursos Humanos',
                'description' => 'Conceptos básicos sobre la gestión de personas en organizaciones.',
                'is_active' => true,
                'questions_order' => 'sequential',
                'time_option' => 'by_question',
                'time' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Liderazgo y Gestión del Talento',
                'description' => 'Preguntas sobre liderazgo, motivación y gestión del desempeño.',
                'is_active' => true,
                'questions_order' => 'random',
                'time_option' => 'by_trivia',
                'time' => 60,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
    }
}
