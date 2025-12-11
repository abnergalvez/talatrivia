<?php

namespace App\Actions\User;

use App\Models\User;
use App\Exceptions\BusinessRuleException;

class DeleteUserAction
{
    public function execute($id)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $user = User::findOrFail($id);

        if ($userAuth->id === $user->id) {
            throw new BusinessRuleException("You cannot delete your own account.", 422);
        }

        if ($user->answers()->exists()) {
            throw new BusinessRuleException("Cannot delete user with game activity. User has {$user->answers()->count()} answer(s) recorded.", 422);
        }

        $triviaCount = $user->trivias()->count();
        if ($triviaCount > 0) {
            $user->trivias()->detach();
        }

        $userName = $user->name;
        $user->delete();

        $message = "User '{$userName}' deleted successfully.";
        if ($triviaCount > 0) {
            $message .= " Dissociated from {$triviaCount} trivia(s).";
        }

        return response()->json([
            'message' => $message
        ], 200);
    }
}