<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LevelsSeeder extends Seeder
{
    public function run()
    {
        DB::table('levels')->truncate();

        $levels = [
            ['name' => 'Fácil', 'points' => 1],
            ['name' => 'Medio', 'points' => 2],
            ['name' => 'Difícil', 'points' => 3],
        ];

        foreach ($levels as $level) {
            DB::table('levels')->insert([
                'name' => $level['name'],
                'points' => $level['points'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
