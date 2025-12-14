<?php

namespace App\Actions\PlayerTrivia;

use App\Models\Trivia;
use App\Models\Question;
use App\Models\Option;
use App\Models\Answer;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnswerAllQuestionsAction
{
    public function execute($triviaId, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth) {
            throw new BusinessRuleException("User not authenticated.", 401);
        }

        $trivia = Trivia::with('questions.level')->findOrFail($triviaId);

        $assignment = $userAuth->trivias()
            ->where('trivia_id', $triviaId)
            ->first();

        if (!$assignment) {
            throw new BusinessRuleException("You do not have access to this trivia.", 403);
        }

        if (!$trivia->is_active) {
            throw new BusinessRuleException("This trivia is currently inactive.", 403);
        }

        if ($assignment->pivot->completed_at) {
            throw new BusinessRuleException("You have already completed this trivia.", 422);
        }

        $answeredQuestionIds = Answer::where('user_id', $userAuth->id)
            ->where('trivia_id', $triviaId)
            ->pluck('question_id')
            ->toArray();

        $triviaQuestionIds = $trivia->questions->pluck('id')->toArray();
        
        foreach ($data['answers'] as $answer) {
            if (!in_array($answer['question_id'], $triviaQuestionIds)) {
                throw new BusinessRuleException(
                    "Question ID {$answer['question_id']} does not belong to this trivia.", 
                    422
                );
            }

            if (in_array($answer['question_id'], $answeredQuestionIds)) {
                throw new BusinessRuleException(
                    "Question ID {$answer['question_id']} has already been answered.", 
                    422
                );
            }
        }

        $questionIds = collect($data['answers'])->pluck('question_id')->toArray();
        if (count($questionIds) !== count(array_unique($questionIds))) {
            throw new BusinessRuleException("Duplicate questions found in your answers.", 422);
        }

        $result = DB::transaction(function () use ($userAuth, $trivia, $data, $assignment, $answeredQuestionIds) {
            $totalPointsEarned = 0;
            $correctAnswers = 0;
            $answersProcessed = [];

            foreach ($data['answers'] as $answerData) {
                $question = $trivia->questions
                    ->where('id', $answerData['question_id'])
                    ->first();

                $option = Option::where('id', $answerData['option_id'])
                    ->where('question_id', $answerData['question_id'])
                    ->first();

                if (!$option) {
                    throw new BusinessRuleException(
                        "Option ID {$answerData['option_id']} does not belong to question ID {$answerData['question_id']}.", 
                        422
                    );
                }

                Answer::create([
                    'user_id' => $userAuth->id,
                    'trivia_id' => $trivia->id,
                    'question_id' => $question->id,
                    'option_id' => $option->id,
                ]);

                $pointsEarned = $option->is_correct ? $question->level->points : 0;
                $totalPointsEarned += $pointsEarned;

                if ($option->is_correct) {
                    $correctAnswers++;
                }

                $answersProcessed[] = [
                    'question_id' => $question->id,
                    'option_id' => $option->id,
                    //'is_correct' => $option->is_correct,
                    'points_earned' => $pointsEarned
                ];
            }

            $currentScore = $assignment->pivot->score ?? 0;
            $newScore = $currentScore + $totalPointsEarned;

            $totalQuestions = $trivia->questions()->count();
            $totalAnsweredQuestions = count($answeredQuestionIds) + count($data['answers']);

            $completedAt = null;
            if ($totalAnsweredQuestions >= $totalQuestions) {
                $completedAt = Carbon::now();
            }

            $userAuth->trivias()->updateExistingPivot($trivia->id, [
                'score' => $newScore,
                'completed_at' => $completedAt
            ]);

            return [
                'answers_processed' => $answersProcessed,
                'total_points_earned' => $totalPointsEarned,
                'correct_answers' => $correctAnswers,
                'total_answers' => count($answersProcessed),
                'total_score' => $newScore,
                'completed' => $completedAt !== null,
                'answered_questions' => $totalAnsweredQuestions,
                'total_questions' => $totalQuestions
            ];
        });

        return response()->json([
            'message' => 'Answers recorded successfully.',
            'result' => $result
        ], 201);
    }
}