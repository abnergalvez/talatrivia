<?php

namespace App\Actions\Question;

use App\Models\Question;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class DeleteQuestionAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $question = Question::with(['options'])->findOrFail($id);

        // Verificar si hay respuestas asociadas a esta pregunta
        $hasAnswers = $question->answers()->exists();

        if ($hasAnswers) {
            throw new BusinessRuleException(
                "Cannot delete question with recorded answers. This question has active game history.", 
                422
            );
        }

        $questionDescription = substr($question->description, 0, 50) . '...';
        $optionsCount = $question->options->count();

        // Borrado en cascada
        DB::transaction(function () use ($question) {
            // Eliminar opciones asociadas
            $question->options()->delete();
            
            // Eliminar la pregunta
            $question->delete();
        });

        $message = "Question deleted successfully.";
        
        if ($optionsCount > 0) {
            $message .= " {$optionsCount} option(s) deleted.";
        }

        return response()->json([
            'message' => $message
        ], 200);
    }
}