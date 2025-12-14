<?php

namespace App\Actions\Trivia;

use App\Models\Trivia;
use App\Exceptions\BusinessRuleException;

class ListTriviaAction
{
    public function execute()
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $trivias = Trivia::withCount(['users', 'questions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'trivias' => $trivias
        ], 200);
    }
}