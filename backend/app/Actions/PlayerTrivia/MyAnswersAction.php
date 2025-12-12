<?php

namespace App\Actions\PlayerTrivia;

use App\Models\Trivia;
use App\Models\Answer;
use App\Exceptions\BusinessRuleException;

class MyAnswersAction
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

        // Obtener las respuestas del usuario para esta trivia
        $answers = Answer::where('user_id', $userAuth->id)
            ->where('trivia_id', $triviaId)
            ->with([
                'question.level',
                'option'
            ])
            ->get()
            ->map(function ($answer) {
                $isCorrect = $answer->option->is_correct;
                $pointsEarned = $isCorrect ? $answer->question->level->points : 0;

                return [
                    'question' => [
                        'id' => $answer->question->id,
                        'description' => $answer->question->description,
                        'level' => [
                            'id' => $answer->question->level->id,
                            'name' => $answer->question->level->name,
                            'points' => $answer->question->level->points,
                        ]
                    ],
                    'selected_option' => [
                        'id' => $answer->option->id,
                        'text' => $answer->option->text,
                        //'is_correct' => $answer->option->is_correct
                    ],
                    //'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                    'answered_at' => $answer->created_at
                ];
            });

        // Calcular estadísticas
        $totalQuestions = $trivia->questions()->count();
        $answeredQuestions = $answers->count();
        $correctAnswers = $answers->where('is_correct', true)->count();
        $incorrectAnswers = $answers->where('is_correct', false)->count();
        $totalScore = $assignment->pivot->score ?? 0;

        return response()->json([
            'trivia' => [
                'id' => $trivia->id,
                'name' => $trivia->name,
                'description' => $trivia->description,
            ],
            'summary' => [
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                //'correct_answers' => $correctAnswers,
                //'incorrect_answers' => $incorrectAnswers,
                'total_score' => $totalScore,
                'completed_at' => $assignment->pivot->completed_at,
                'status' => $assignment->pivot->completed_at ? 'completed' : 'in_progress'
            ],
            'answers' => $answers
        ], 200);
    }
}