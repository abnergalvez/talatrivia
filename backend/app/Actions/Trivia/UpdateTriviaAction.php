<?php

namespace App\Actions\Trivia;

use App\Models\Trivia;
use App\Exceptions\BusinessRuleException;

class UpdateTriviaAction
{
    public function execute($id, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $trivia = Trivia::findOrFail($id);

        $trivia->update($data);

        return response()->json([
            'message' => 'Trivia updated successfully.',
            'trivia' => $trivia->load(['questions', 'users'])
        ], 200);
    }
}