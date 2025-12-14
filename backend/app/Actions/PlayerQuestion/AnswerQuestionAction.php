<?php

namespace App\Actions\PlayerQuestion;

use App\Models\Question;
use App\Models\Option;
use App\Models\Answer;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnswerQuestionAction
{
    public function execute($questionId, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth) {
            throw new BusinessRuleException("User not authenticated.", 401);
        }

        $question = Question::with(['trivia', 'level'])->findOrFail($questionId);
        $trivia = $question->trivia;

        $assignment = $userAuth->trivias()
            ->where('trivia_id', $trivia->id)
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

        $existingAnswer = Answer::where('user_id', $userAuth->id)
            ->where('question_id', $questionId)
            ->first();

        if ($existingAnswer) {
            throw new BusinessRuleException("You have already answered this question.", 422);
        }

        $option = Option::where('id', $data['option_id'])
            ->where('question_id', $questionId)
            ->first();

        if (!$option) {
            throw new BusinessRuleException("The selected option does not belong to this question.", 422);
        }

        $result = DB::transaction(function () use ($userAuth, $trivia, $question, $option, $assignment) {
            $answer = Answer::create([
                'user_id' => $userAuth->id,
                'trivia_id' => $trivia->id,
                'question_id' => $question->id,
                'option_id' => $option->id,
            ]);

            $pointsEarned = $option->is_correct ? $question->level->points : 0;

            $currentScore = $assignment->pivot->score ?? 0;
            $newScore = $currentScore + $pointsEarned;

            $totalQuestions = $trivia->questions()->count();
            $answeredQuestions = Answer::where('user_id', $userAuth->id)
                ->where('trivia_id', $trivia->id)
                ->count();

            $completedAt = null;
            if ($answeredQuestions >= $totalQuestions) {
                $completedAt = Carbon::now();
            }

            $userAuth->trivias()->updateExistingPivot($trivia->id, [
                'score' => $newScore,
                'completed_at' => $completedAt
            ]);

            return [
                'answer' => $answer,
                'points_earned' => $pointsEarned,
                'total_score' => $newScore,
                'is_correct' => $option->is_correct,
                'completed' => $completedAt !== null,
                'answered_questions' => $answeredQuestions,
                'total_questions' => $totalQuestions
            ];
        });

        return response()->json([
            'message' => 'Answer recorded successfully.',
            'result' => $result
        ], 201);
    }
}