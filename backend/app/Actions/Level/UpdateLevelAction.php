<?php

namespace App\Actions\Level;

use App\Models\Level;
use App\Exceptions\BusinessRuleException;

class UpdateLevelAction
{
    public function execute($id, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $level = Level::findOrFail($id);

        if (in_array($level->name, ['Fácil', 'Medio', 'Difícil'])) {
            throw new BusinessRuleException("Cannot modify system levels (Fácil, Medio, Difícil).", 422);
        }

        $level->update($data);

        return response()->json([
            'message' => 'Level updated successfully.',
            'level' => $level
        ], 200);
    }
}