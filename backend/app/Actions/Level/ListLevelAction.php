<?php

namespace App\Actions\Level;

use App\Models\Level;
use App\Exceptions\BusinessRuleException;

class ListLevelAction
{
    public function execute()
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $levels = Level::withCount('questions')->orderBy('points', 'asc')->get();

        return response()->json([
            'levels' => $levels
        ], 200);
    }
}