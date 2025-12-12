<?php

namespace App\Actions\Question;

use App\Models\Question;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class UpdateQuestionAction
{
    public function execute($id, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $question = Question::findOrFail($id);

        // Actualizar la pregunta y opciones en una transacción
        DB::transaction(function () use ($question, $data) {
            // Si se envían opciones, actualizar/reemplazar
            if (isset($data['options'])) {
                $options = $data['options'];
                unset($data['options']);

                // Eliminar opciones antiguas
                $question->options()->delete();

                // Crear las nuevas opciones
                foreach ($options as $optionData) {
                    $question->options()->create($optionData);
                }
            }

            // Actualizar los campos de la pregunta
            if (!empty($data)) {
                $question->update($data);
            }
        });

        // Recargar relaciones
        $question->load(['level', 'options', 'trivia']);

        return response()->json([
            'message' => 'Question updated successfully.',
            'question' => $question
        ], 200);
    }
}