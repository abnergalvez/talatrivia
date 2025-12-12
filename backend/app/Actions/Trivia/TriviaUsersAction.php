<?php

namespace App\Actions\Trivia;

use App\Models\Trivia;
use App\Exceptions\BusinessRuleException;

class TriviaUsersAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $trivia = Trivia::findOrFail($id);

        $users = $trivia->users()
            ->withPivot('score', 'completed_at')
            ->with('role')
            ->get();

        return response()->json([
            'trivia' => [
                'id' => $trivia->id,
                'name' => $trivia->name
            ],
            'users' => $users,
            'total_users' => $users->count()
        ], 200);
    }
}