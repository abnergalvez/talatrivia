<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RoleAndUserSeeder::class,
            LevelsSeeder::class,
            TriviaSeeder::class,
            QuestionSeeder::class,
            OptionSeeder::class,
            TriviaUserSeeder::class,
            AnswerSeeder::class,
        ]);
    }
}
