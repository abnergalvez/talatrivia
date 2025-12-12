<?php

namespace App\Actions\Trivia;

use App\Models\Trivia;
use App\Models\User;
use App\Exceptions\BusinessRuleException;

class UnassignTriviaFromUsersAction
{
    public function execute($triviaId, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $trivia = Trivia::findOrFail($triviaId);

        // Resolver usuarios
        $userIds = isset($data['user_ids']) && !empty($data['user_ids'])
            ? $data['user_ids']
            : User::whereIn('email', $data['emails'] ?? [])->pluck('id')->toArray();

        if (empty($userIds)) {
            throw new BusinessRuleException("No valid users found to unassign.", 422);
        }

        // Desasignar usuarios
        $trivia->users()->detach($userIds);

        $unassignedUsers = User::whereIn('id', $userIds)->get(['id', 'name', 'email']);

        return response()->json([
            'message' => 'Users unassigned from trivia successfully.',
            'trivia' => [
                'id' => $trivia->id,
                'name' => $trivia->name
            ],
            'unassigned_users' => $unassignedUsers,
            'total_unassigned' => count($userIds)
        ], 200);
    }
}