<?php

namespace App\Actions\Level;

use App\Models\Level;
use App\Exceptions\BusinessRuleException;

class DeleteLevelAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $level = Level::findOrFail($id);

        if (in_array($level->name, ['Fácil', 'Medio', 'Difícil'])) {
            throw new BusinessRuleException("Cannot delete system levels (Fácil, Medio, Difícil).", 422);
        }

        if ($level->questions()->exists()) {
            throw new BusinessRuleException("Cannot delete level with assigned questions. Level has {$level->questions()->count()} question(s).", 422);
        }

        $levelName = $level->name;
        $level->delete();

        return response()->json([
            'message' => "Level '{$levelName}' deleted successfully."
        ], 200);
    }
}