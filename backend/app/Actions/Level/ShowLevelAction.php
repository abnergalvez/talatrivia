<?php

namespace App\Actions\Level;

use App\Models\Level;
use App\Exceptions\BusinessRuleException;

class ShowLevelAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $level = Level::withCount('questions')->findOrFail($id);

        return response()->json([
            'level' => $level
        ], 200);
    }
}