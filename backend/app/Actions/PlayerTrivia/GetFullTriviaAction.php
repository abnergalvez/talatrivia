<?php

namespace App\Actions\PlayerTrivia;

use App\Models\Trivia;
use App\Models\Answer;
use App\Exceptions\BusinessRuleException;

class GetFullTriviaAction
{
    public function execute($triviaId)
    {
        $userAuth = app('user');
        
        if (!$userAuth) {
            throw new BusinessRuleException("User not authenticated.", 401);
        }

        // Verificar que la trivia existe
        $trivia = Trivia::findOrFail($triviaId);

        // Verificar que el usuario tiene asignada esta trivia
        $assignment = $userAuth->trivias()
            ->where('trivia_id', $triviaId)
            ->first();

        if (!$assignment) {
            throw new BusinessRuleException("You do not have access to this trivia.", 403);
        }

        // Verificar si la trivia está activa
        if (!$trivia->is_active) {
            throw new BusinessRuleException("This trivia is currently inactive and cannot be accessed.", 403);
        }

        // Verificar si el usuario ya completó la trivia
        if ($assignment->pivot->completed_at) {
            throw new BusinessRuleException("You have already completed this trivia.", 422);
        }

        // Obtener IDs de preguntas ya respondidas por el usuario
        $answeredQuestionIds = Answer::where('user_id', $userAuth->id)
            ->where('trivia_id', $triviaId)
            ->pluck('question_id')
            ->toArray();

        // Obtener preguntas de la trivia
        $questionsQuery = $trivia->questions()
            ->with(['level', 'options' => function ($query) {
                // No mostrar is_correct al usuario
                $query->select('id', 'question_id', 'text');
            }])
            ->whereNotIn('id', $answeredQuestionIds); // Solo preguntas no respondidas

        // Aplicar ordenamiento según configuración
        if ($trivia->questions_order === 'random') {
            $questionsQuery->inRandomOrder();
        } else {
            $questionsQuery->orderBy('id', 'asc');
        }

        $questions = $questionsQuery->get()->map(function ($question) {
            return [
                'id' => $question->id,
                'description' => $question->description,
                'time' => $question->time,
                'level' => [
                    'id' => $question->level->id,
                    'name' => $question->level->name,
                    'points' => $question->level->points,
                ],
                'options' => $question->options
            ];
        });

        return response()->json([
            'trivia' => [
                'id' => $trivia->id,
                'name' => $trivia->name,
                'description' => $trivia->description,
                'questions_order' => $trivia->questions_order,
                'time_option' => $trivia->time_option,
                'time' => $trivia->time,
            ],
            'questions' => $questions,
            'total_questions' => $questions->count(),
            'answered_questions' => count($answeredQuestionIds),
            'remaining_questions' => $questions->count()
        ], 200);
    }
}