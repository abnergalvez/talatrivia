<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OptionSeeder extends Seeder
{
    public function run()
    {
        DB::table('options')->truncate();

        $questions = DB::table('questions')->get();

        foreach ($questions as $q) {

            $options = match($q->description) {

                '¿Qué es el reclutamiento en Recursos Humanos?' => [
                    ['text' => 'El proceso de atraer candidatos para un puesto', 'is_correct' => true],
                    ['text' => 'El proceso de capacitar empleados', 'is_correct' => false],
                    ['text' => 'La evaluación mensual del desempeño', 'is_correct' => false],
                ],

                '¿Cuál es el objetivo principal de una entrevista laboral?' => [
                    ['text' => 'Determinar si el candidato se ajusta al puesto y a la empresa', 'is_correct' => true],
                    ['text' => 'Conocer la vida personal del entrevistado', 'is_correct' => false],
                    ['text' => 'Descartar candidatos automáticamente', 'is_correct' => false],
                ],

                '¿Qué se entiende por “clima laboral”?' => [
                    ['text' => 'El ambiente emocional y social que se vive dentro de la empresa', 'is_correct' => true],
                    ['text' => 'La temperatura en las oficinas', 'is_correct' => false],
                    ['text' => 'El número de empleados contratados por mes', 'is_correct' => false],
                ],

                '¿Qué característica distingue a un líder efectivo?' => [
                    ['text' => 'La capacidad de influir positivamente en su equipo', 'is_correct' => true],
                    ['text' => 'Dar órdenes sin recibir retroalimentación', 'is_correct' => false],
                    ['text' => 'Ser más antiguo que los demás', 'is_correct' => false],
                ],

                '¿Qué es la evaluación de desempeño?' => [
                    ['text' => 'Un proceso para medir el rendimiento y aportar retroalimentación', 'is_correct' => true],
                    ['text' => 'Una encuesta sobre satisfacción del cliente', 'is_correct' => false],
                    ['text' => 'Un taller de capacitación anual', 'is_correct' => false],
                ],

                default => []
            };

            foreach ($options as $op) {
                DB::table('options')->insert([
                    'question_id' => $q->id,
                    'text' => $op['text'],
                    'is_correct' => $op['is_correct'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }
    }
}
