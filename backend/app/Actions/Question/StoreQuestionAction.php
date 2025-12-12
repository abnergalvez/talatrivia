<?php

namespace App\Actions\Question;

use App\Models\Trivia;
use App\Models\Question;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class StoreQuestionAction
{
    public function execute($triviaId, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        // Verificar que la trivia existe
        $trivia = Trivia::findOrFail($triviaId);

        // Crear la pregunta y sus opciones en una transacción
        $question = DB::transaction(function () use ($triviaId, $data) {
            // Extraer las opciones del array de datos
            $options = $data['options'];
            unset($data['options']);

            // Agregar trivia_id al array de datos
            $data['trivia_id'] = $triviaId;

            // Crear la pregunta
            $question = Question::create($data);

            // Crear las opciones asociadas
            foreach ($options as $optionData) {
                $question->options()->create($optionData);
            }

            // Cargar las relaciones para la respuesta
            $question->load(['level', 'options']);

            return $question;
        });

        return response()->json([
            'message' => 'Question created successfully.',
            'question' => $question
        ], 201);
    }
}