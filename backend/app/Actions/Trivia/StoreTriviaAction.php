<?php

namespace App\Actions\Trivia;

use App\Models\Trivia;
use App\Exceptions\BusinessRuleException;

class StoreTriviaAction
{
    public function execute(array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $data['is_active'] = $data['is_active'] ?? true;

        $trivia = Trivia::create($data);

        return response()->json([
            'message' => 'Trivia created successfully.',
            'trivia' => $trivia
        ], 201);
    }
}