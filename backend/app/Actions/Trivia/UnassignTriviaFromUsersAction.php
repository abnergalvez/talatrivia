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

        $userIds = isset($data['user_ids']) && !empty($data['user_ids'])
            ? $data['user_ids']
            : User::whereIn('email', $data['emails'] ?? [])->pluck('id')->toArray();

        if (empty($userIds)) {
            throw new BusinessRuleException("No valid users found to unassign.", 422);
        }

        // detecta usuarios con respuestas en la trivia
        $usersWithAnswers = User::whereIn('id', $userIds)
            ->whereHas('answers', function ($q) use ($triviaId) {
                $q->where('trivia_id', $triviaId);
            })
            ->pluck('id')
            ->toArray();

        if (!empty($usersWithAnswers)) {
            $blocked = User::whereIn('id', $usersWithAnswers)
                ->get(['id', 'name', 'email']);

            throw new BusinessRuleException("Some users cannot be unassigned because they already answered this trivia.", 422);
        }

        $userIdsToUnassign = array_diff($userIds, $usersWithAnswers);

        if (empty($userIdsToUnassign)) {
            throw new BusinessRuleException("No users can be unassigned from this trivia.", 422);
        }

        $trivia->users()->detach($userIdsToUnassign);

        $unassignedUsers = User::whereIn('id', $userIdsToUnassign)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'message' => 'Users unassigned from trivia successfully.',
            'trivia' => [
                'id' => $trivia->id,
                'name' => $trivia->name
            ],
            'unassigned_users' => $unassignedUsers,
            'total_unassigned' => count($userIdsToUnassign)
        ], 200);
    }
}