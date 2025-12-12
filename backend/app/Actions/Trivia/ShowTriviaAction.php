<?php

namespace App\Actions\Trivia;

use App\Models\Trivia;
use App\Exceptions\BusinessRuleException;

class ShowTriviaAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $trivia = Trivia::with(['questions', 'users'])
            ->withCount(['users', 'questions'])
            ->findOrFail($id);

        return response()->json([
            'trivia' => $trivia
        ], 200);
    }
}