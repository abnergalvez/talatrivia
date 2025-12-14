<?php

namespace App\Actions\Level;

use App\Models\Level;
use App\Exceptions\BusinessRuleException;

class StoreLevelAction
{
    public function execute(array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $level = Level::create($data);

        return response()->json([
            'message' => 'Level created successfully.',
            'level' => $level
        ], 201);
    }
}