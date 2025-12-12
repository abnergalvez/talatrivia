<?php

namespace App\Actions\Trivia;

use App\Models\Trivia;
use App\Models\User;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class AssignTriviaToUsersAction
{
    public function execute($triviaId, array $data)
    {
        $userAuth = app('user');
        
        if (!$userAuth?->isAdmin()) {
            throw new BusinessRuleException("Only admin users can perform this action.", 403);
        }

        $trivia = Trivia::findOrFail($triviaId);

        if (!$trivia->is_active) {
            throw new BusinessRuleException("Cannot assign users to an inactive trivia.", 422);
        }

        $users = $this->resolveUsers($data);

        if ($users->isEmpty()) {
            throw new BusinessRuleException("No valid users found to assign.", 422);
        }

        $alreadyAssigned = [];
        $newAssignments = [];

        foreach ($users as $user) {
            if ($trivia->users()->where('user_id', $user->id)->exists()) {
                $alreadyAssigned[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ];
            } else {
                $newAssignments[] = $user->id;
            }
        }

        if (!empty($newAssignments)) {
            $trivia->users()->attach($newAssignments);
        }

        $assignedUsers = User::whereIn('id', $newAssignments)->get(['id', 'name', 'email']);

        return response()->json([
            'message' => 'Trivia assignment completed.',
            'trivia' => [
                'id' => $trivia->id,
                'name' => $trivia->name
            ],
            'newly_assigned' => $assignedUsers,
            'already_assigned' => $alreadyAssigned,
            'summary' => [
                'total_requested' => $users->count(),
                'newly_assigned' => count($newAssignments),
                'already_assigned' => count($alreadyAssigned)
            ]
        ], 200);
    }

    private function resolveUsers(array $data)
    {
        if (isset($data['user_ids']) && !empty($data['user_ids'])) {
            return User::whereIn('id', $data['user_ids'])->get();
        }

        if (isset($data['emails']) && !empty($data['emails'])) {
            return User::whereIn('email', $data['emails'])->get();
        }

        return collect();
    }
}