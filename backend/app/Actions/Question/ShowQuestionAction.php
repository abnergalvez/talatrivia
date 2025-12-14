<?php

namespace App\Actions\Question;

use App\Models\Question;
use App\Exceptions\BusinessRuleException;

class ShowQuestionAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $question = Question::with(['trivia', 'level', 'options'])
            ->withCount('options')
            ->findOrFail($id);

        return response()->json([
            'question' => $question
        ], 200);
    }
}