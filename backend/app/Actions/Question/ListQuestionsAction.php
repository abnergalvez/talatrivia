<?php

namespace App\Actions\Question;

use App\Models\Trivia;
use App\Exceptions\BusinessRuleException;

class ListQuestionsAction
{
    public function execute($triviaId)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $trivia = Trivia::findOrFail($triviaId);

        $questions = $trivia->questions()
            ->with(['level', 'options'])
            ->withCount('options')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'trivia' => [
                'id' => $trivia->id,
                'name' => $trivia->name,
            ],
            'questions' => $questions,
            'total_questions' => $questions->count()
        ], 200);
    }
}